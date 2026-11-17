<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

// Anggota yang pernah bertransaksi dinonaktifkan (status nonaktif + tidak bisa login);
// yang belum pernah bertransaksi dihapus permanen beserta akun login dan fotonya.
$id = (int) ($_POST['id'] ?? 0);
if (is_post()) {
    csrf_check();
    $a = db_row('SELECT * FROM anggota WHERE id = ?', [$id]);
    if (!$a) {
        flash('gagal', 'Anggota tidak ditemukan.');
        redirect('modules/master/anggota/index.php');
    }
    if (($_POST['aksi'] ?? '') === 'aktifkan') {
        db()->prepare("UPDATE anggota SET status = 'aktif' WHERE id = ? AND kode_anggota IS NOT NULL")->execute([$id]);
        db()->prepare('UPDATE users SET is_active = 1 WHERE id = ?')->execute([$a['user_id']]);
        flash('sukses', 'Anggota diaktifkan kembali.');
        redirect('modules/master/anggota/detail.php?id=' . $id);
    }
    $punya_transaksi = db_val('SELECT 1 FROM peminjaman WHERE anggota_id = ? UNION SELECT 1 FROM reservasi WHERE anggota_id = ? LIMIT 1', [$id, $id]);
    if ($punya_transaksi) {
        if (db_val("SELECT 1 FROM peminjaman WHERE anggota_id = ? AND status = 'aktif'", [$id])) {
            flash('gagal', 'Anggota masih punya pinjaman aktif.');
            redirect('modules/master/anggota/detail.php?id=' . $id);
        }
        db()->prepare("UPDATE anggota SET status = 'nonaktif' WHERE id = ?")->execute([$id]);
        db()->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([$a['user_id']]);
        flash('sukses', 'Anggota pernah bertransaksi, jadi dinonaktifkan (tidak dihapus).');
        redirect('modules/master/anggota/detail.php?id=' . $id);
    }
    db()->beginTransaction();
    db()->prepare('DELETE FROM anggota WHERE id = ?')->execute([$id]);
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$a['user_id']]);
    db()->commit();
    hapus_gambar('anggota', $a['foto']);
    flash('sukses', 'Anggota dihapus.');
}
redirect('modules/master/anggota/index.php');
