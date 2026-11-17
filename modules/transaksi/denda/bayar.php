<?php
require __DIR__ . '/../../../includes/init.php';
require_role('petugas');

if (is_post()) {
    csrf_check();
    try {
        $no = bayar_denda((array) ($_POST['denda_ids'] ?? []), user()['id']);
        flash('sukses', "Denda lunas. Kuitansi $no.");
        redirect('modules/transaksi/denda/kuitansi.php?no=' . urlencode($no));
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect('modules/transaksi/denda/index.php?anggota=' . urlencode(post('anggota')));
