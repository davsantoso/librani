<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

if (is_post()) {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['aksi'] ?? '') === 'aktifkan') {
        db()->prepare('UPDATE rak SET is_active = 1 WHERE id = ?')->execute([$id]);
        flash('sukses', 'Rak diaktifkan kembali.');
    } else {
        $hasil = hapus_atau_nonaktifkan('rak', $id, [['eksemplar', 'rak_id']]);
        flash('sukses', $hasil === 'dihapus' ? 'Rak dihapus.' : 'Rak sudah berisi eksemplar, jadi dinonaktifkan.');
    }
}
redirect('modules/master/rak/index.php');
