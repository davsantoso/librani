<?php
// JSON: cari anggota untuk form peminjaman. GET ?q=<kode/nama>
require __DIR__ . '/../../../includes/init.php';
require_role('petugas', 'admin');

header('Content-Type: application/json; charset=utf-8');
$hasil = array_map(fn($a) => [
    'id' => $a['id'],
    'kode_anggota' => $a['kode_anggota'],
    'nama' => $a['nama'],
    'status' => $a['status'],
    'tgl_berlaku' => tgl($a['tgl_berlaku']),
    'sisa_kuota' => $a['sisa_kuota'],
    'maks_pinjam' => $a['maks_pinjam'],
    'masalah' => $a['masalah'],
], cari_anggota_pinjam((string) ($_GET['q'] ?? '')));
echo json_encode($hasil, JSON_UNESCAPED_UNICODE);
