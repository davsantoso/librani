<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$f = filter_laporan($_GET);
tampilkan_laporan('Laporan Anggota', $f, laporan_anggota($f), [
    'periode_daftar', 'status' => ['menunggu' => 'Menunggu', 'aktif' => 'Aktif', 'ditolak' => 'Ditolak', 'nonaktif' => 'Nonaktif'],
]);
