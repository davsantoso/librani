<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$f = filter_laporan($_GET);
tampilkan_laporan('Laporan Denda', $f, laporan_denda($f), [
    'periode', 'anggota', 'status' => ['belum_lunas' => 'Belum lunas', 'lunas' => 'Lunas', 'dibebaskan' => 'Dibebaskan'],
]);
