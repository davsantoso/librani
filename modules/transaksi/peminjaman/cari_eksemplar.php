<?php
// JSON: info eksemplar untuk form peminjaman. GET ?kode=<kode eksemplar>&anggota_id=<id>
require __DIR__ . '/../../../includes/init.php';
require_role('petugas', 'admin');

header('Content-Type: application/json; charset=utf-8');
$x = info_eksemplar_pinjam((string) ($_GET['kode'] ?? ''), (int) ($_GET['anggota_id'] ?? 0));
echo json_encode($x ?? ['error' => 'Kode eksemplar tidak ditemukan.'], JSON_UNESCAPED_UNICODE);
