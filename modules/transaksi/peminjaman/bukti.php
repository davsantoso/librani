<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$p = data_peminjaman((int) ($_GET['id'] ?? 0));
if (!$p) {
    http_response_code(404);
    exit('Peminjaman tidak ditemukan.');
}

$judul_cetak = 'BUKTI PEMINJAMAN';
$sub_judul = $p['no_transaksi'];
$kertas = 'A5';
require ROOT_DIR . '/includes/layout/print_header.php';
?>
<table class="mb-3 small">
    <tr><td style="width:130px">No. transaksi</td><td>: <strong><?= e($p['no_transaksi']) ?></strong></td></tr>
    <tr><td>Tanggal pinjam</td><td>: <?= tgl($p['tgl_pinjam']) ?></td></tr>
    <tr><td>Anggota</td><td>: <?= e($p['anggota']) ?> (<?= e($p['kode_anggota']) ?>)</td></tr>
</table>
<table class="cetak">
    <thead><tr><th>No</th><th>Kode eksemplar</th><th>Judul</th><th>Jatuh tempo</th></tr></thead>
    <tbody>
    <?php foreach ($p['item'] as $n => $i): ?>
        <tr><td><?= $n + 1 ?></td><td><?= e($i['kode_eksemplar']) ?></td><td><?= e($i['judul']) ?></td><td><?= tgl($i['tgl_jatuh_tempo']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p class="small mt-3">Keterlambatan dikenakan denda <?= rupiah((int) ($p['item'][0]['denda_per_hari'] ?? 0)) ?> per eksemplar per hari.</p>
<div class="d-flex justify-content-between">
    <div class="ttd ms-0">Peminjam<br><br><br><br><?= e($p['anggota']) ?></div>
    <div class="ttd">Petugas<br><br><br><br><?= e($p['petugas']) ?></div>
</div>
<?php require ROOT_DIR . '/includes/layout/print_footer.php'; ?>
