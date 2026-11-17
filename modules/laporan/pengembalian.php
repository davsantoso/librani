<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$f = filter_laporan($_GET);
tampilkan_laporan('Laporan Pengembalian', $f, laporan_pengembalian($f), ['periode', 'ketepatan']);
