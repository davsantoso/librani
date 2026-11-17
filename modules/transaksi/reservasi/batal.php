<?php
require __DIR__ . '/../../../includes/init.php';
require_anggota_aktif();

if (is_post()) {
    csrf_check();
    try {
        batal_reservasi((int) ($_POST['id'] ?? 0), anggota_saya()['id']);
        flash('sukses', 'Reservasi dibatalkan.');
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect('modules/anggota/reservasi.php');
