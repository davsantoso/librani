<?php
// Reservasi (P1): antrean per judul, siapa cepat dia dapat.
// Alur status: menunggu → siap (eksemplar dikunci, ada batas ambil) → diambil (lewat peminjaman)
//              atau batal (oleh anggota) / kadaluarsa (lewat batas ambil).
// Tidak ada cron di hosting gratis, jadi cek_reservasi_kadaluarsa() dipanggil saat halaman terkait dibuka.

/** @return int id reservasi */
function buat_reservasi(int $anggota_id, int $buku_id, ?string $tanggal = null): int
{
    $tanggal ??= hari_ini();
    $db = db();
    $db->beginTransaction();
    try {
        db_val('SELECT id FROM anggota WHERE id = ? FOR UPDATE', [$anggota_id]);
        $masalah = syarat_pinjam($anggota_id, $tanggal);
        if ($masalah) {
            throw new RuntimeException($masalah[0]);
        }
        if (!db_val('SELECT 1 FROM buku WHERE id = ? AND is_active = 1', [$buku_id])) {
            throw new RuntimeException('Buku tidak ditemukan.');
        }
        $st = db_row(
            "SELECT SUM(status = 'tersedia') AS tersedia, SUM(status IN ('dipinjam','direservasi')) AS beredar FROM eksemplar WHERE buku_id = ?",
            [$buku_id]
        );
        if ((int) $st['tersedia'] > 0) {
            throw new RuntimeException('Masih ada eksemplar tersedia, silakan pinjam langsung di meja layanan.');
        }
        if ((int) $st['beredar'] === 0) {
            throw new RuntimeException('Judul ini tidak punya eksemplar yang bisa dipinjam.');
        }
        if (db_val(
            "SELECT 1 FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id JOIN eksemplar e ON e.id = d.eksemplar_id
             WHERE p.anggota_id = ? AND e.buku_id = ? AND d.status = 'dipinjam'",
            [$anggota_id, $buku_id]
        )) {
            throw new RuntimeException('Anda sedang meminjam judul ini.');
        }
        if (db_val("SELECT 1 FROM reservasi WHERE anggota_id = ? AND buku_id = ? AND status IN ('menunggu','siap')", [$anggota_id, $buku_id])) {
            throw new RuntimeException('Anda sudah mereservasi judul ini.');
        }
        $maks = (int) setting('maks_reservasi');
        if ((int) db_val("SELECT COUNT(*) FROM reservasi WHERE anggota_id = ? AND status IN ('menunggu','siap')", [$anggota_id]) >= $maks) {
            throw new RuntimeException("Batas reservasi aktif ($maks judul) tercapai.");
        }
        $db->prepare('INSERT INTO reservasi (anggota_id, buku_id, tgl_reservasi) VALUES (?, ?, ?)')
            ->execute([$anggota_id, $buku_id, $tanggal . ' ' . date('H:i:s')]);
        $id = (int) $db->lastInsertId();
        $db->commit();
        return $id;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/** Anggota membatalkan reservasinya sendiri selama belum diambil. */
function batal_reservasi(int $reservasi_id, int $anggota_id): void
{
    $db = db();
    $db->beginTransaction();
    try {
        $r = db_row('SELECT * FROM reservasi WHERE id = ? AND anggota_id = ? FOR UPDATE', [$reservasi_id, $anggota_id]);
        if (!$r) {
            throw new RuntimeException('Reservasi tidak ditemukan.');
        }
        if (!in_array($r['status'], ['menunggu', 'siap'], true)) {
            throw new RuntimeException('Reservasi ini tidak bisa dibatalkan.');
        }
        $db->prepare("UPDATE reservasi SET status = 'batal' WHERE id = ?")->execute([$reservasi_id]);
        if ($r['status'] === 'siap') {
            lepaskan_eksemplar((int) $r['eksemplar_id']);
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Eksemplar yang baru tersedia diberikan ke antrean tertua untuk judulnya (jika ada).
 * @return bool true jika dialokasikan (eksemplar menjadi 'direservasi')
 */
function alokasikan_reservasi(int $eksemplar_id, ?string $tanggal = null): bool
{
    $tanggal ??= hari_ini();
    // Hanya eksemplar yang bebas (tersedia, atau dilepas dari reservasi lain) yang boleh dikunci untuk antrean.
    $status = db_val('SELECT status FROM eksemplar WHERE id = ? FOR UPDATE', [$eksemplar_id]);
    if (!in_array($status, ['tersedia', 'direservasi'], true)
        || ($status === 'direservasi' && db_val("SELECT 1 FROM reservasi WHERE eksemplar_id = ? AND status = 'siap'", [$eksemplar_id]))) {
        return false;
    }
    $r = db_row(
        "SELECT r.id FROM reservasi r JOIN eksemplar e ON e.buku_id = r.buku_id
         WHERE e.id = ? AND r.status = 'menunggu' ORDER BY r.tgl_reservasi, r.id LIMIT 1 FOR UPDATE",
        [$eksemplar_id]
    );
    if (!$r) {
        return false;
    }
    db()->prepare("UPDATE reservasi SET status = 'siap', eksemplar_id = ?, tgl_siap = ?, tgl_batas_ambil = ? WHERE id = ?")
        ->execute([$eksemplar_id, $tanggal, tambah_hari($tanggal, (int) setting('batas_ambil_reservasi')), $r['id']]);
    db()->prepare("UPDATE eksemplar SET status = 'direservasi' WHERE id = ?")->execute([$eksemplar_id]);
    return true;
}

/** Eksemplar yang dilepas reservasi: ke antrean berikutnya, atau kembali tersedia. */
function lepaskan_eksemplar(int $eksemplar_id, ?string $tanggal = null): void
{
    if (!alokasikan_reservasi($eksemplar_id, $tanggal)) {
        db()->prepare("UPDATE eksemplar SET status = 'tersedia' WHERE id = ? AND status = 'direservasi'")->execute([$eksemplar_id]);
    }
}

/** Reservasi 'siap' yang lewat batas ambil menjadi kadaluarsa. @return int jumlah yang kadaluarsa */
function cek_reservasi_kadaluarsa(?string $tanggal = null): int
{
    $tanggal ??= hari_ini();
    $jumlah = 0;
    foreach (db_all("SELECT id FROM reservasi WHERE status = 'siap' AND tgl_batas_ambil < ?", [$tanggal]) as $r) {
        $jumlah += (int) kadaluarsakan_reservasi((int) $r['id'], $tanggal);
    }
    return $jumlah;
}

/**
 * Kadaluarsakan satu reservasi 'siap' yang lewat batas ambil, lalu lepaskan eksemplarnya.
 * Status dicek ulang dengan baris terkunci, jadi pemanggilan basi (reservasi sudah diambil/dibatalkan
 * oleh request lain) tidak mengubah apa pun. @return bool true jika benar-benar dikadaluarsakan
 */
function kadaluarsakan_reservasi(int $reservasi_id, string $tanggal): bool
{
    $db = db();
    $db->beginTransaction();
    try {
        $r = db_row("SELECT eksemplar_id FROM reservasi WHERE id = ? AND status = 'siap' AND tgl_batas_ambil < ? FOR UPDATE", [$reservasi_id, $tanggal]);
        if ($r) {
            $db->prepare("UPDATE reservasi SET status = 'kadaluarsa' WHERE id = ?")->execute([$reservasi_id]);
            lepaskan_eksemplar((int) $r['eksemplar_id'], $tanggal);
        }
        $db->commit();
        return (bool) $r;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/** Reservasi milik anggota beserta posisi antrean (untuk yang menunggu). */
function reservasi_anggota(int $anggota_id): array
{
    return db_all(
        "SELECT r.*, b.judul, b.penulis, b.foto_thumb, b.foto_cover, e.kode_eksemplar,
                (SELECT COUNT(*) FROM reservasi r2 WHERE r2.buku_id = r.buku_id AND r2.status = 'menunggu'
                    AND (r2.tgl_reservasi < r.tgl_reservasi OR (r2.tgl_reservasi = r.tgl_reservasi AND r2.id <= r.id))) AS posisi
         FROM reservasi r JOIN buku b ON b.id = r.buku_id LEFT JOIN eksemplar e ON e.id = r.eksemplar_id
         WHERE r.anggota_id = ? ORDER BY r.status IN ('menunggu','siap') DESC, r.tgl_reservasi DESC",
        [$anggota_id]
    );
}
