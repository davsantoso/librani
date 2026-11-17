<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

if (is_post()) {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    try {
        hapus_eksemplar($id);
        flash('sukses', 'Eksemplar dihapus.');
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect('modules/master/eksemplar/index.php');
