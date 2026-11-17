<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

if (is_post()) {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['aksi'] ?? '') === 'aktifkan') {
        db()->prepare('UPDATE buku SET is_active = 1 WHERE id = ?')->execute([$id]);
        flash('sukses', 'Buku diaktifkan kembali.');
    } else {
        $foto = db_val('SELECT foto_cover FROM buku WHERE id = ?', [$id]);
        $hasil = hapus_atau_nonaktifkan('buku', $id, [['eksemplar', 'buku_id'], ['reservasi', 'buku_id']]);
        if ($hasil === 'dihapus') {
            hapus_gambar('cover', $foto ?: null);
        }
        flash('sukses', $hasil === 'dihapus' ? 'Buku dihapus.' : 'Buku memiliki eksemplar, jadi dinonaktifkan dan disembunyikan dari katalog.');
    }
}
redirect('modules/master/buku/index.php');
