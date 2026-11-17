<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$f = filter_laporan($_GET);
tampilkan_laporan('Laporan Koleksi', $f, laporan_koleksi($f), [
    'kategori', 'rak', 'status' => ['tersedia' => 'Ada yang tersedia', 'dipinjam' => 'Ada yang dipinjam', 'direservasi' => 'Ada yang direservasi', 'rusak' => 'Ada yang rusak', 'hilang' => 'Ada yang hilang'],
]);
