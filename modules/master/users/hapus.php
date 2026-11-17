<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

if (is_post()) {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id === user()['id']) {
        flash('gagal', 'Tidak bisa menghapus akun sendiri.');
    } elseif (!db_val("SELECT 1 FROM users WHERE id = ? AND role IN ('admin','petugas')", [$id])) {
        flash('gagal', 'User tidak ditemukan.');
    } else {
        $hasil = hapus_atau_nonaktifkan('users', $id, [
            ['peminjaman', 'petugas_id'], ['pengembalian', 'petugas_id'], ['denda', 'petugas_id'],
            ['anggota', 'verified_by'], ['perpanjangan', 'diajukan_oleh'],
        ]);
        flash('sukses', $hasil === 'dihapus' ? 'User dihapus.' : 'User pernah memproses transaksi, jadi dinonaktifkan.');
    }
}
redirect('modules/master/users/index.php');
