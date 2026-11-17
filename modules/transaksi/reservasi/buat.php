<?php
require __DIR__ . '/../../../includes/init.php';
require_anggota_aktif();

$buku_id = (int) ($_POST['buku_id'] ?? 0);
if (is_post()) {
    csrf_check();
    try {
        buat_reservasi(anggota_saya()['id'], $buku_id);
        flash('sukses', 'Reservasi dibuat. Anda akan diberi tahu di dashboard saat buku siap diambil.');
        redirect('modules/anggota/reservasi.php');
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect('modules/katalog/detail.php?id=' . $buku_id);
