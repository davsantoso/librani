<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

if (is_post()) {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['aksi'] ?? '') === 'aktifkan') {
        db()->prepare('UPDATE penerbit SET is_active = 1 WHERE id = ?')->execute([$id]);
        flash('sukses', 'Penerbit diaktifkan kembali.');
    } else {
        $hasil = hapus_atau_nonaktifkan('penerbit', $id, [['buku', 'penerbit_id']]);
        flash('sukses', $hasil === 'dihapus' ? 'Penerbit dihapus.' : 'Penerbit sudah dipakai buku, jadi dinonaktifkan.');
    }
}
redirect('modules/master/penerbit/index.php');
