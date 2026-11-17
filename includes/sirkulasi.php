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
