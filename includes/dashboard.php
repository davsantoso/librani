<?php
// Query ringkasan untuk dashboard. Semua menerima $tanggal agar bisa diuji dengan tanggal tetap.

/** Kartu angka dashboard admin/petugas. */
function statistik_sirkulasi(?string $tanggal = null): array
{
    $t = $tanggal ?? hari_ini();
    $r = db_row(
        "SELECT
            (SELECT COUNT(*) FROM buku WHERE is_active = 1) AS total_judul,
            (SELECT COUNT(*) FROM eksemplar WHERE status NOT IN ('hilang')) AS total_eksemplar,
            (SELECT COUNT(*) FROM eksemplar WHERE status = 'dipinjam') AS dipinjam,
            (SELECT COUNT(*) FROM peminjaman WHERE tgl_pinjam = ?) AS pinjam_hari_ini,
            (SELECT COUNT(*) FROM peminjaman_detail WHERE status = 'dipinjam' AND tgl_jatuh_tempo < ?) AS terlambat,
            (SELECT COALESCE(SUM(jumlah), 0) FROM denda WHERE status = 'belum_lunas') AS denda_belum_lunas,
            (SELECT COUNT(*) FROM anggota WHERE status = 'menunggu') AS menunggu_verifikasi,
            (SELECT COUNT(*) FROM reservasi WHERE status = 'siap') AS reservasi_siap",
        [$t, $t]
    );
    return array_map('intval', $r);
}

/** Jumlah transaksi peminjaman per hari, 30 hari terakhir s.d. $tanggal (hari kosong = 0). */
function grafik_30_hari(?string $tanggal = null): array
{
    $akhir = $tanggal ?? hari_ini();
    $awal = tambah_hari($akhir, -29);
    $ada = array_column(
        db_all('SELECT tgl_pinjam, COUNT(*) AS n FROM peminjaman WHERE tgl_pinjam BETWEEN ? AND ? GROUP BY tgl_pinjam', [$awal, $akhir]),
        'n',
        'tgl_pinjam'
    );
    $hasil = [];
    for ($i = 0; $i < 30; $i++) {
        $t = tambah_hari($awal, $i);
        $hasil[] = ['tgl' => $t, 'jumlah' => (int) ($ada[$t] ?? 0)];
    }
    return $hasil;
}

/** Judul paling sering dipinjam (per eksemplar) dalam periode. */
function buku_populer(string $dari, string $sampai, int $limit = 5): array
{
    return db_all(
        'SELECT b.id, b.judul, b.penulis, b.foto_thumb, b.foto_cover, COUNT(*) AS jumlah
         FROM peminjaman_detail d
         JOIN peminjaman p ON p.id = d.peminjaman_id
         JOIN eksemplar e ON e.id = d.eksemplar_id
         JOIN buku b ON b.id = e.buku_id
         WHERE p.tgl_pinjam BETWEEN ? AND ?
         GROUP BY b.id ORDER BY jumlah DESC, b.judul LIMIT ' . (int) $limit,
        [$dari, $sampai]
    );
}

/** Pinjaman yang jatuh tempo pada $tanggal dan yang sudah terlambat. */
function jatuh_tempo_dan_terlambat(?string $tanggal = null): array
{
    $t = $tanggal ?? hari_ini();
    $sql = "SELECT d.id, d.tgl_jatuh_tempo, p.no_transaksi, a.nama AS anggota, a.kode_anggota, b.judul, e.kode_eksemplar,
                   DATEDIFF(?, d.tgl_jatuh_tempo) AS hari_terlambat
            FROM peminjaman_detail d
            JOIN peminjaman p ON p.id = d.peminjaman_id
            JOIN anggota a ON a.id = p.anggota_id
            JOIN eksemplar e ON e.id = d.eksemplar_id
            JOIN buku b ON b.id = e.buku_id
            WHERE d.status = 'dipinjam' AND d.tgl_jatuh_tempo ";
    return [
        'hari_ini' => db_all($sql . '= ? ORDER BY a.nama', [$t, $t]),
        'terlambat' => db_all($sql . '< ? ORDER BY d.tgl_jatuh_tempo, a.nama', [$t, $t]),
    ];
}

/** Ringkasan untuk dashboard anggota. */
function ringkasan_anggota(int $anggota_id, ?string $tanggal = null): array
{
    $t = $tanggal ?? hari_ini();
    $aktif = db_all(
        "SELECT d.id, d.tgl_jatuh_tempo, d.jumlah_perpanjangan, p.no_transaksi, e.kode_eksemplar, b.judul, b.penulis, b.foto_thumb, b.foto_cover
         FROM peminjaman_detail d
         JOIN peminjaman p ON p.id = d.peminjaman_id
         JOIN eksemplar e ON e.id = d.eksemplar_id
         JOIN buku b ON b.id = e.buku_id
         WHERE p.anggota_id = ? AND d.status = 'dipinjam' ORDER BY d.tgl_jatuh_tempo",
        [$anggota_id]
    );
    foreach ($aktif as &$x) {
        $x['terlambat'] = $x['tgl_jatuh_tempo'] < $t;
    }
    unset($x);
    $maks = (int) setting('maks_pinjam');
    return [
        'aktif' => $aktif,
        'maks_pinjam' => $maks,
        'sisa_kuota' => max(0, $maks - count($aktif)),
        'denda_belum_lunas' => denda_belum_lunas($anggota_id),
    ];
}
