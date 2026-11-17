<?php
// Aturan bisnis sirkulasi: peminjaman, pengembalian, denda, perpanjangan.
// Hanya fungsi di file ini (dan reservasi.php) yang mengubah status eksemplar saat transaksi.

/** Jumlah eksemplar yang sedang dipinjam anggota dan sudah lewat jatuh tempo pada $tanggal. */
function pinjaman_terlambat(int $anggota_id, ?string $tanggal = null): int
{
    return (int) db_val(
        "SELECT COUNT(*) FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id
         WHERE p.anggota_id = ? AND d.status = 'dipinjam' AND d.tgl_jatuh_tempo < ?",
        [$anggota_id, $tanggal ?? hari_ini()]
    );
}

function denda_belum_lunas(int $anggota_id): int
{
    return (int) db_val("SELECT COALESCE(SUM(jumlah), 0) FROM denda WHERE anggota_id = ? AND status = 'belum_lunas'", [$anggota_id]);
}

function jumlah_pinjaman_aktif(int $anggota_id): int
{
    return (int) db_val(
        "SELECT COUNT(*) FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id
         WHERE p.anggota_id = ? AND d.status = 'dipinjam'",
        [$anggota_id]
    );
}

/** @return string[] alasan anggota tidak boleh meminjam (kosong = boleh). */
function syarat_pinjam(int $anggota_id, ?string $tanggal = null): array
{
    $tanggal ??= hari_ini();
    $a = db_row('SELECT status, tgl_berlaku FROM anggota WHERE id = ?', [$anggota_id]);
    if (!$a) {
        return ['Anggota tidak ditemukan.'];
    }
    $masalah = [];
    if ($a['status'] !== 'aktif') {
        $masalah[] = 'Anggota belum aktif.';
    } elseif (!$a['tgl_berlaku'] || $a['tgl_berlaku'] < $tanggal) {
        $masalah[] = 'Masa berlaku keanggotaan habis.';
    }
    $denda = denda_belum_lunas($anggota_id);
    if ($denda > 0) {
        $masalah[] = 'Masih ada denda belum lunas (' . rupiah($denda) . ').';
    }
    if (pinjaman_terlambat($anggota_id, $tanggal) > 0) {
        $masalah[] = 'Masih ada pinjaman terlambat.';
    }
    return $masalah;
}

/**
 * Simpan transaksi peminjaman (header + detail). Semua atau tidak sama sekali.
 * @param int[] $eksemplar_ids
 * @return int id peminjaman
 */
function simpan_peminjaman(int $anggota_id, array $eksemplar_ids, int $petugas_id, ?string $tanggal = null): int
{
    $tanggal ??= hari_ini();
    $eksemplar_ids = array_map('intval', $eksemplar_ids);
    if (!$eksemplar_ids) {
        throw new RuntimeException('Pilih minimal satu eksemplar.');
    }
    if (count($eksemplar_ids) !== count(array_unique($eksemplar_ids))) {
        throw new RuntimeException('Eksemplar yang sama dipilih dua kali.');
    }

    $db = db();
    $db->beginTransaction();
    try {
        // Kunci baris anggota agar dua transaksi bersamaan untuk anggota yang sama tidak melewati batas pinjam.
        db_val('SELECT id FROM anggota WHERE id = ? FOR UPDATE', [$anggota_id]);
        $masalah = syarat_pinjam($anggota_id, $tanggal);
        if ($masalah) {
            throw new RuntimeException($masalah[0]);
        }
        foreach ($eksemplar_ids as $eid) {
            $x = db_row('SELECT id, kode_eksemplar, status FROM eksemplar WHERE id = ? FOR UPDATE', [$eid]);
            if (!$x) {
                throw new RuntimeException('Eksemplar tidak ditemukan.');
            }
            if (!eksemplar_boleh_dipinjam($x, $anggota_id)) {
                throw new RuntimeException("Eksemplar {$x['kode_eksemplar']} tidak tersedia ({$x['status']}).");
            }
        }
        $maks = (int) setting('maks_pinjam');
        $aktif = jumlah_pinjaman_aktif($anggota_id);
        if ($aktif + count($eksemplar_ids) > $maks) {
            throw new RuntimeException("Melebihi batas pinjam $maks eksemplar (sedang dipinjam: $aktif).");
        }

        $no = nomor_berikutnya('PJM', 'peminjaman', 'no_transaksi', $tanggal);
        $db->prepare('INSERT INTO peminjaman (no_transaksi, anggota_id, petugas_id, tgl_pinjam) VALUES (?, ?, ?, ?)')
            ->execute([$no, $anggota_id, $petugas_id, $tanggal]);
        $pid = (int) $db->lastInsertId();

        $jatuh_tempo = tambah_hari($tanggal, (int) setting('lama_pinjam'));
        $denda_per_hari = (int) setting('denda_per_hari');
        $ins = $db->prepare('INSERT INTO peminjaman_detail (peminjaman_id, eksemplar_id, tgl_jatuh_tempo, denda_per_hari) VALUES (?, ?, ?, ?)');
        $upd = $db->prepare("UPDATE eksemplar SET status = 'dipinjam' WHERE id = ?");
        foreach ($eksemplar_ids as $eid) {
            $ins->execute([$pid, $eid, $jatuh_tempo, $denda_per_hari]);
            $upd->execute([$eid]);
        }
        ambil_reservasi_saat_pinjam($anggota_id, $eksemplar_ids);
        $db->commit();
        return $pid;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/** Eksemplar bisa dipinjam jika tersedia, atau direservasi untuk anggota ini (reservasi siap diambil). */
function eksemplar_boleh_dipinjam(array $eksemplar, int $anggota_id): bool
{
    if ($eksemplar['status'] === 'tersedia') {
        return true;
    }
    return $eksemplar['status'] === 'direservasi' && db_val(
        "SELECT 1 FROM reservasi WHERE eksemplar_id = ? AND anggota_id = ? AND status = 'siap'",
        [$eksemplar['id'], $anggota_id]
    );
}

/** Reservasi 'siap' milik anggota untuk eksemplar yang dipinjam berubah menjadi 'diambil'. */
function ambil_reservasi_saat_pinjam(int $anggota_id, array $eksemplar_ids): void
{
    $st = db()->prepare("UPDATE reservasi SET status = 'diambil' WHERE anggota_id = ? AND eksemplar_id = ? AND status = 'siap'");
    foreach ($eksemplar_ids as $eid) {
        $st->execute([$anggota_id, $eid]);
    }
}

// ===================== Pencarian untuk form transaksi =====================

/** Anggota yang cocok dengan kode (persis) atau nama/kode (sebagian), beserta sisa kuota dan alasan tidak boleh pinjam. */
function cari_anggota_pinjam(string $q, ?string $tanggal = null): array
{
    $q = trim($q);
    if ($q === '') {
        return [];
    }
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $rows = db_all(
        "SELECT id, kode_anggota, nama, status, tgl_berlaku, foto FROM anggota
         WHERE kode_anggota = ? OR kode_anggota LIKE ? OR nama LIKE ?
         ORDER BY kode_anggota = ? DESC, nama LIMIT 10",
        [strtoupper($q), $like, $like, strtoupper($q)]
    );
    $maks = (int) setting('maks_pinjam');
    return array_map(fn($a) => $a + [
        'sisa_kuota' => max(0, $maks - jumlah_pinjaman_aktif($a['id'])),
        'maks_pinjam' => $maks,
        'masalah' => syarat_pinjam($a['id'], $tanggal),
    ], $rows);
}

/** Info eksemplar berdasarkan kode untuk form peminjaman; null jika kode tidak dikenal. */
function info_eksemplar_pinjam(string $kode, int $anggota_id): ?array
{
    $x = db_row(
        'SELECT e.id, e.kode_eksemplar, e.status, b.judul, b.penulis, b.foto_thumb
         FROM eksemplar e JOIN buku b ON b.id = e.buku_id WHERE e.kode_eksemplar = ?',
        [strtoupper(trim($kode))]
    );
    if (!$x) {
        return null;
    }
    $boleh = eksemplar_boleh_dipinjam($x, $anggota_id);
    return [
        'id' => $x['id'],
        'kode_eksemplar' => $x['kode_eksemplar'],
        'judul' => $x['judul'],
        'penulis' => $x['penulis'],
        'thumb_url' => $x['foto_thumb'] ? url('uploads/cover/thumb/' . $x['foto_thumb']) : null,
        'boleh' => $boleh,
        'alasan' => $boleh ? '' : "Eksemplar sedang {$x['status']}.",
    ];
}

/** Header peminjaman + daftar item (dengan buku, sampul, pengembalian). Dipakai halaman detail & bukti. */
function data_peminjaman(int $id): ?array
{
    $p = db_row(
        'SELECT p.*, a.nama AS anggota, a.kode_anggota, a.no_hp, u.nama AS petugas
         FROM peminjaman p JOIN anggota a ON a.id = p.anggota_id JOIN users u ON u.id = p.petugas_id WHERE p.id = ?',
        [$id]
    );
    if (!$p) {
        return null;
    }
    $p['item'] = db_all(
        'SELECT d.*, e.kode_eksemplar, b.judul, b.penulis, b.foto_thumb, b.foto_cover,
                k.no_pengembalian, k.tgl_kembali, k.kondisi, k.hari_terlambat
         FROM peminjaman_detail d
         JOIN eksemplar e ON e.id = d.eksemplar_id
         JOIN buku b ON b.id = e.buku_id
         LEFT JOIN pengembalian k ON k.peminjaman_detail_id = d.id
         WHERE d.peminjaman_id = ? ORDER BY d.id',
        [$id]
    );
    return $p;
}

// ===================== Pengembalian =====================

/** Hari terlambat (hari kalender) dan denda = max(0, kembali - jatuh tempo) x denda per hari. */
function hitung_denda_terlambat(string $jatuh_tempo, string $tgl_kembali, int $denda_per_hari): array
{
    $hari = max(0, (int) (new DateTime($jatuh_tempo))->diff(new DateTime($tgl_kembali))->format('%r%a'));
    return ['hari' => $hari, 'denda' => $hari * $denda_per_hari];
}

/** Denda rusak/hilang dari harga buku sesuai persen pengaturan (0 jika harga kosong). */
function denda_kondisi(string $kondisi, int $harga): int
{
    if ($kondisi === 'baik' || $harga <= 0) {
        return 0;
    }
    $persen = (int) setting($kondisi === 'rusak' ? 'denda_rusak_persen' : 'denda_hilang_persen');
    return (int) round($harga * $persen / 100);
}

/** Perkiraan denda sebelum disimpan, per detail peminjaman yang masih dipinjam. */
function pratinjau_kembali(array $detail_ids, ?string $tanggal = null): array
{
    $tanggal ??= hari_ini();
    $hasil = [];
    foreach (array_map('intval', $detail_ids) as $id) {
        $d = db_row(
            "SELECT d.id, d.tgl_jatuh_tempo, d.denda_per_hari, e.kode_eksemplar, b.judul, b.harga
             FROM peminjaman_detail d JOIN eksemplar e ON e.id = d.eksemplar_id JOIN buku b ON b.id = e.buku_id
             WHERE d.id = ? AND d.status = 'dipinjam'",
            [$id]
        );
        if (!$d) {
            continue;
        }
        $t = hitung_denda_terlambat($d['tgl_jatuh_tempo'], $tanggal, (int) $d['denda_per_hari']);
        $hasil[] = [
            'detail_id' => $d['id'], 'kode_eksemplar' => $d['kode_eksemplar'], 'judul' => $d['judul'],
            'tgl_jatuh_tempo' => $d['tgl_jatuh_tempo'], 'hari' => $t['hari'], 'denda_terlambat' => $t['denda'],
            'denda_rusak' => denda_kondisi('rusak', (int) $d['harga']), 'denda_hilang' => denda_kondisi('hilang', (int) $d['harga']),
        ];
    }
    return $hasil;
}

/**
 * Proses pengembalian satu atau beberapa eksemplar dalam satu nomor KMB.
 * $items = [['detail_id' => int, 'kondisi' => 'baik'|'rusak'|'hilang'], ...]
 * @return string nomor pengembalian
 */
function proses_kembali(array $items, int $petugas_id, ?string $tanggal = null): string
{
    $tanggal ??= hari_ini();
    if (!$items) {
        throw new RuntimeException('Pilih minimal satu eksemplar yang dikembalikan.');
    }
    $db = db();
    $db->beginTransaction();
    try {
        $no = nomor_berikutnya('KMB', 'pengembalian', 'no_pengembalian', $tanggal);
        $header = [];
        foreach ($items as $it) {
            $kondisi = (string) ($it['kondisi'] ?? '');
            if (!in_array($kondisi, ['baik', 'rusak', 'hilang'], true)) {
                throw new RuntimeException('Kondisi harus baik, rusak, atau hilang.');
            }
            $d = db_row(
                'SELECT d.*, p.anggota_id, p.tgl_pinjam, e.kode_eksemplar, b.harga
                 FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id
                 JOIN eksemplar e ON e.id = d.eksemplar_id JOIN buku b ON b.id = e.buku_id
                 WHERE d.id = ? FOR UPDATE',
                [(int) ($it['detail_id'] ?? 0)]
            );
            if (!$d) {
                throw new RuntimeException('Data peminjaman tidak ditemukan.');
            }
            if ($d['status'] !== 'dipinjam') {
                throw new RuntimeException("Eksemplar {$d['kode_eksemplar']} sudah dikembalikan.");
            }
            if ($tanggal < $d['tgl_pinjam']) {
                throw new RuntimeException('Tanggal kembali tidak boleh sebelum tanggal pinjam.');
            }

            $t = hitung_denda_terlambat($d['tgl_jatuh_tempo'], $tanggal, (int) $d['denda_per_hari']);
            $db->prepare('INSERT INTO pengembalian (no_pengembalian, peminjaman_detail_id, tgl_kembali, kondisi, hari_terlambat, petugas_id) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$no, $d['id'], $tanggal, $kondisi, $t['hari'], $petugas_id]);
            $kid = (int) $db->lastInsertId();

            $denda = db()->prepare('INSERT INTO denda (pengembalian_id, anggota_id, jenis, jumlah, keterangan) VALUES (?, ?, ?, ?, ?)');
            if ($t['denda'] > 0) {
                $denda->execute([$kid, $d['anggota_id'], 'terlambat', $t['denda'], "Terlambat {$t['hari']} hari x " . rupiah((int) $d['denda_per_hari'])]);
            }
            $dk = denda_kondisi($kondisi, (int) $d['harga']);
            if ($dk > 0) {
                $denda->execute([$kid, $d['anggota_id'], $kondisi, $dk, "Buku $kondisi (" . setting("denda_{$kondisi}_persen") . '% dari ' . rupiah((int) $d['harga']) . ')']);
            }

            $db->prepare("UPDATE peminjaman_detail SET status = 'kembali' WHERE id = ?")->execute([$d['id']]);
            $status_eksemplar = ['baik' => 'tersedia', 'rusak' => 'rusak', 'hilang' => 'hilang'][$kondisi];
            $db->prepare('UPDATE eksemplar SET status = ? WHERE id = ?')->execute([$status_eksemplar, $d['eksemplar_id']]);
            $header[$d['peminjaman_id']] = true;
        }
        foreach (array_keys($header) as $pid) {
            $db->prepare("UPDATE peminjaman SET status = 'selesai'
                          WHERE id = ? AND NOT EXISTS (SELECT 1 FROM peminjaman_detail WHERE peminjaman_id = ? AND status = 'dipinjam')")
                ->execute([$pid, $pid]);
        }
        $db->commit();
        return $no;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Item yang masih dipinjam untuk form pengembalian. $q = no. transaksi (item transaksi itu),
 * kode anggota, atau kode eksemplar (semua pinjaman aktif milik anggota tersebut).
 */
function cari_pinjaman_aktif(string $q): array
{
    $q = strtoupper(trim($q));
    if ($q === '') {
        return [];
    }
    $sql = "SELECT d.id AS detail_id, d.tgl_jatuh_tempo, d.denda_per_hari, p.id AS peminjaman_id, p.no_transaksi, p.tgl_pinjam,
                   a.id AS anggota_id, a.nama AS anggota, a.kode_anggota, e.kode_eksemplar, b.judul, b.penulis, b.harga, b.foto_thumb, b.foto_cover
            FROM peminjaman_detail d
            JOIN peminjaman p ON p.id = d.peminjaman_id
            JOIN anggota a ON a.id = p.anggota_id
            JOIN eksemplar e ON e.id = d.eksemplar_id
            JOIN buku b ON b.id = e.buku_id
            WHERE d.status = 'dipinjam' AND ";
    if (str_starts_with($q, 'PJM-')) {
        return db_all($sql . 'p.no_transaksi = ? ORDER BY d.id', [$q]);
    }
    $anggota_id = db_val('SELECT id FROM anggota WHERE kode_anggota = ?', [$q])
        ?: db_val("SELECT p.anggota_id FROM eksemplar e JOIN peminjaman_detail d ON d.eksemplar_id = e.id AND d.status = 'dipinjam'
                   JOIN peminjaman p ON p.id = d.peminjaman_id WHERE e.kode_eksemplar = ?", [$q]);
    return $anggota_id ? db_all($sql . 'p.anggota_id = ? ORDER BY d.tgl_jatuh_tempo, d.id', [$anggota_id]) : [];
}

// ===================== Denda =====================

/** Lunasi satu atau beberapa denda milik SATU anggota sekaligus. @return string nomor kuitansi BYR-... */
function bayar_denda(array $denda_ids, int $petugas_id, ?string $tanggal = null): string
{
    $tanggal ??= hari_ini();
    $ids = array_values(array_unique(array_map('intval', $denda_ids)));
    if (!$ids) {
        throw new RuntimeException('Pilih minimal satu denda.');
    }
    $db = db();
    $db->beginTransaction();
    try {
        $tanda = implode(',', array_fill(0, count($ids), '?'));
        $rows = db_all("SELECT id, anggota_id, status FROM denda WHERE id IN ($tanda) FOR UPDATE", $ids);
        if (count($rows) !== count($ids)) {
            throw new RuntimeException('Data denda tidak ditemukan.');
        }
        if (count(array_unique(array_column($rows, 'anggota_id'))) > 1) {
            throw new RuntimeException('Pembayaran hanya untuk denda satu anggota.');
        }
        foreach ($rows as $r) {
            if ($r['status'] !== 'belum_lunas') {
                throw new RuntimeException('Hanya denda yang belum lunas yang bisa dibayar.');
            }
        }
        $no = nomor_berikutnya('BYR', 'denda', 'no_kuitansi', $tanggal);
        $db->prepare("UPDATE denda SET status = 'lunas', tgl_bayar = ?, petugas_id = ?, no_kuitansi = ? WHERE id IN ($tanda)")
            ->execute([$tanggal, $petugas_id, $no, ...$ids]);
        $db->commit();
        return $no;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/** Bebaskan denda (khusus admin), alasan wajib dan dicatat di keterangan. */
function bebaskan_denda(int $denda_id, int $admin_id, string $alasan): void
{
    if (trim($alasan) === '') {
        throw new RuntimeException('Alasan pembebasan wajib diisi.');
    }
    $st = db()->prepare(
        "UPDATE denda SET status = 'dibebaskan', petugas_id = ?, tgl_bayar = ?,
                keterangan = CONCAT(COALESCE(keterangan, ''), ' | Dibebaskan: ', ?)
         WHERE id = ? AND status = 'belum_lunas'"
    );
    $st->execute([$admin_id, hari_ini(), trim($alasan), $denda_id]);
    if ($st->rowCount() === 0) {
        throw new RuntimeException('Hanya denda yang belum lunas yang bisa dibebaskan.');
    }
}

// ===================== Perpanjangan (P1) =====================

/** Alasan detail pinjaman tidak bisa diperpanjang oleh $user_id pada $tanggal; null jika bisa. */
function alasan_tidak_bisa_perpanjang(int $detail_id, int $user_id, ?string $tanggal = null): ?string
{
    $tanggal ??= hari_ini();
    $d = db_row(
        'SELECT d.*, p.anggota_id, a.user_id AS user_anggota, e.buku_id
         FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id
         JOIN anggota a ON a.id = p.anggota_id JOIN eksemplar e ON e.id = d.eksemplar_id WHERE d.id = ?',
        [$detail_id]
    );
    if (!$d) {
        return 'Data pinjaman tidak ditemukan.';
    }
    $role = db_val('SELECT role FROM users WHERE id = ?', [$user_id]);
    if ($role === 'anggota' && (int) $d['user_anggota'] !== $user_id) {
        return 'Bukan pinjaman Anda.';
    }
    if ($d['status'] !== 'dipinjam') {
        return 'Pinjaman sudah dikembalikan.';
    }
    if ($d['tgl_jatuh_tempo'] < $tanggal) {
        return 'Sudah terlambat, tidak bisa diperpanjang.';
    }
    $maks = (int) setting('maks_perpanjangan');
    if ((int) $d['jumlah_perpanjangan'] >= $maks) {
        return "Sudah mencapai batas perpanjangan ($maks kali).";
    }
    $masalah = syarat_pinjam((int) $d['anggota_id'], $tanggal);
    if ($masalah) {
        return $masalah[0];
    }
    if (db_val("SELECT 1 FROM reservasi WHERE buku_id = ? AND anggota_id <> ? AND status = 'menunggu' LIMIT 1", [$d['buku_id'], $d['anggota_id']])) {
        return 'Judul ini sedang direservasi anggota lain.';
    }
    return null;
}

/** Perpanjang jatuh tempo satu eksemplar. @return string jatuh tempo baru */
function perpanjang(int $detail_id, int $user_id, ?string $tanggal = null): string
{
    $db = db();
    $db->beginTransaction();
    try {
        db_val('SELECT id FROM peminjaman_detail WHERE id = ? FOR UPDATE', [$detail_id]);
        $alasan = alasan_tidak_bisa_perpanjang($detail_id, $user_id, $tanggal);
        if ($alasan) {
            throw new RuntimeException($alasan);
        }
        $lama = (string) db_val('SELECT tgl_jatuh_tempo FROM peminjaman_detail WHERE id = ?', [$detail_id]);
        $baru = tambah_hari($lama, (int) setting('lama_perpanjangan'));
        $db->prepare('UPDATE peminjaman_detail SET tgl_jatuh_tempo = ?, jumlah_perpanjangan = jumlah_perpanjangan + 1 WHERE id = ?')
            ->execute([$baru, $detail_id]);
        $db->prepare('INSERT INTO perpanjangan (peminjaman_detail_id, jatuh_tempo_lama, jatuh_tempo_baru, tgl_perpanjangan, diajukan_oleh) VALUES (?, ?, ?, ?, ?)')
            ->execute([$detail_id, $lama, $baru, $tanggal ?? hari_ini(), $user_id]);
        $db->commit();
        return $baru;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}
