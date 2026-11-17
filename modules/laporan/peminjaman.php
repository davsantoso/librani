<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$f = filter_laporan($_GET);
tampilkan_laporan('Laporan Peminjaman', $f, laporan_peminjaman($f), [
    'periode', 'anggota', 'kategori', 'status' => ['dipinjam' => 'Masih dipinjam', 'terlambat' => 'Terlambat', 'kembali' => 'Sudah kembali'],
]);
