<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$buku_id = 0;
if (is_post()) {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $buku_id = (int) db_val('SELECT buku_id FROM eksemplar WHERE id = ?', [$id]);
    try {
        tandai_eksemplar($id, (string) ($_POST['status'] ?? ''));
        flash('sukses', 'Status eksemplar diubah menjadi ' . $_POST['status'] . '.');
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect('modules/master/eksemplar/index.php' . ($buku_id ? '?buku_id=' . $buku_id : ''));
