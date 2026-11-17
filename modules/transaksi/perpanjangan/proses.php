<?php
// POST: perpanjang satu eksemplar. Anggota hanya miliknya sendiri (dicek di perpanjang()); petugas untuk siapa pun.
require __DIR__ . '/../../../includes/init.php';
require_role('anggota', 'petugas');

if (is_post()) {
    csrf_check();
    try {
        $baru = perpanjang((int) ($_POST['detail_id'] ?? 0), user()['id']);
        flash('sukses', 'Pinjaman diperpanjang. Jatuh tempo baru ' . tgl($baru) . '.');
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
}
redirect(user()['role'] === 'anggota' ? 'modules/anggota/pinjaman.php' : 'modules/transaksi/perpanjangan/index.php');
