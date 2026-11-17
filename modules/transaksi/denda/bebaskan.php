<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

if (is_post()) {
    csrf_check();
    try {
        bebaskan_denda((int) ($_POST['id'] ?? 0), user()['id'], post('alasan'));
        flash('sukses', 'Denda dibebaskan.');
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect('modules/transaksi/denda/index.php');
