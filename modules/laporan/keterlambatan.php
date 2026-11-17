<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$f = filter_laporan($_GET);
// Keterlambatan aktif dihitung per hari ini, tidak memakai periode.
tampilkan_laporan('Laporan Keterlambatan Aktif', $f, laporan_keterlambatan($f), ['kategori', 'min_terlambat']);
