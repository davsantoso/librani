<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

if (is_post()) {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['aksi'] ?? '') === 'aktifkan') {
        db()->prepare('UPDATE kategori SET is_active = 1 WHERE id = ?')->execute([$id]);
        flash('sukses', 'Kategori diaktifkan kembali.');
    } else {
        $hasil = hapus_atau_nonaktifkan('kategori', $id, [['buku', 'kategori_id']]);
        flash('sukses', $hasil === 'dihapus' ? 'Kategori dihapus.' : 'Kategori sudah dipakai buku, jadi dinonaktifkan.');
    }
}
redirect('modules/master/kategori/index.php');
