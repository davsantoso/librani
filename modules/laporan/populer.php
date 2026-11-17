<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$f = filter_laporan($_GET);
tampilkan_laporan('Rekap Buku Terpopuler dan Anggota Teraktif', $f, laporan_populer($f), ['periode']);
