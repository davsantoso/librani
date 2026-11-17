<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$id = (int) ($_POST['id'] ?? 0);
if (is_post()) {
    csrf_check();
    try {
        switch ($_POST['aksi'] ?? '') {
            case 'setujui':
                $kode = verifikasi_anggota($id, user()['id']);
                flash('sukses', "Anggota disetujui dengan kode $kode.");
                break;
            case 'tolak':
                tolak_anggota($id, user()['id'], post('alasan'));
                flash('sukses', 'Pendaftaran ditolak.');
                break;
            case 'perpanjang':
                perpanjang_keanggotaan($id);
                flash('sukses', 'Masa berlaku diperpanjang ' . setting('masa_berlaku_anggota') . ' hari.');
                break;
        }
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect('modules/master/anggota/detail.php?id=' . $id);
