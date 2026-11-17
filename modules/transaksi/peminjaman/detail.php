<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$p = data_peminjaman((int) ($_GET['id'] ?? 0));
if (!$p) {
    flash('gagal', 'Peminjaman tidak ditemukan.');
    redirect('modules/transaksi/peminjaman/index.php');
}
$petugas = user()['role'] === 'petugas';

$judul = 'Peminjaman ' . $p['no_transaksi'];
require ROOT_DIR . '/includes/layout/header.php';
?>
<a class="btn btn-sm btn-link px-0 mb-2" href="<?= url('modules/transaksi/peminjaman/index.php') ?>"><i class="bi bi-arrow-left"></i> Daftar peminjaman</a>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1"><?= e($p['no_transaksi']) ?>
            <span class="badge <?= $p['status'] === 'aktif' ? 'text-bg-warning' : 'text-bg-secondary' ?> fs-6 align-middle"><?= e($p['status']) ?></span></h1>
        <div class="text-muted">Dipinjam <?= tgl($p['tgl_pinjam']) ?> oleh <strong><?= e($p['anggota']) ?></strong>
            (<span class="font-monospace"><?= e($p['kode_anggota']) ?></span>) · dilayani <?= e($p['petugas']) ?></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= url('modules/transaksi/peminjaman/bukti.php?id=' . $p['id']) ?>" target="_blank"><i class="bi bi-printer"></i> Cetak bukti</a>
        <?php if ($petugas && $p['status'] === 'aktif' && is_file(ROOT_DIR . '/modules/transaksi/pengembalian/form.php')): ?>
            <a class="btn btn-primary" href="<?= url('modules/transaksi/pengembalian/form.php?q=' . urlencode($p['no_transaksi'])) ?>"><i class="bi bi-box-arrow-in-down-left"></i> Proses pengembalian</a>
        <?php endif; ?>
    </div>
</div>
<div class="d-grid gap-2">
    <?php foreach ($p['item'] as $i): ?>
        <?php $telat = $i['status'] === 'dipinjam' && $i['tgl_jatuh_tempo'] < hari_ini(); ?>
        <div class="card shadow-sm"><div class="card-body d-flex gap-3 align-items-center">
            <div class="cover-kecil" style="width:56px"><?= cover_html($i) ?></div>
            <div class="flex-grow-1">
                <div class="fw-semibold"><?= e($i['judul']) ?></div>
                <div class="small text-muted"><?= e($i['penulis']) ?> · <span class="font-monospace"><?= e($i['kode_eksemplar']) ?></span></div>
                <div class="small">Jatuh tempo: <span class="<?= $telat ? 'text-danger fw-semibold' : '' ?>"><?= tgl($i['tgl_jatuh_tempo']) ?></span>
                    <?= $i['jumlah_perpanjangan'] ? ' · diperpanjang ' . (int) $i['jumlah_perpanjangan'] . 'x' : '' ?>
                    · denda <?= rupiah((int) $i['denda_per_hari']) ?>/hari</div>
            </div>
            <div class="text-end">
                <?php if ($i['tgl_kembali']): ?>
                    <span class="badge text-bg-success">Kembali <?= tgl($i['tgl_kembali']) ?></span>
                    <div class="small text-muted"><?= e($i['kondisi']) ?><?= $i['hari_terlambat'] ? ', terlambat ' . (int) $i['hari_terlambat'] . ' hari' : '' ?></div>
                <?php elseif ($telat): ?>
                    <span class="badge text-bg-danger">Terlambat</span>
                <?php else: ?>
                    <span class="badge text-bg-warning">Dipinjam</span>
                <?php endif; ?>
            </div>
        </div></div>
    <?php endforeach; ?>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
