<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$no = (string) ($_GET['no'] ?? '');
$item = db_all(
    "SELECT dn.*, a.nama AS anggota, a.kode_anggota, e.kode_eksemplar, b.judul, u.nama AS penerima
     FROM denda dn
     JOIN anggota a ON a.id = dn.anggota_id
     JOIN pengembalian k ON k.id = dn.pengembalian_id
     JOIN peminjaman_detail d ON d.id = k.peminjaman_detail_id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     JOIN buku b ON b.id = e.buku_id
     LEFT JOIN users u ON u.id = dn.petugas_id
     WHERE dn.no_kuitansi = ? ORDER BY dn.id",
    [$no]
);
if (!$item) {
    http_response_code(404);
    exit('Kuitansi tidak ditemukan.');
}
$h = $item[0];
$total = array_sum(array_column($item, 'jumlah'));

$judul_cetak = 'KUITANSI PEMBAYARAN DENDA';
$sub_judul = $h['no_kuitansi'];
$kertas = 'A5';
require ROOT_DIR . '/includes/layout/print_header.php';
?>
<table class="mb-3 small">
    <tr><td style="width:130px">No. kuitansi</td><td>: <strong><?= e($h['no_kuitansi']) ?></strong></td></tr>
    <tr><td>Tanggal bayar</td><td>: <?= tgl($h['tgl_bayar']) ?></td></tr>
    <tr><td>Diterima dari</td><td>: <?= e($h['anggota']) ?> (<?= e($h['kode_anggota']) ?>)</td></tr>
</table>
<table class="cetak">
    <thead><tr><th>No</th><th>Jenis</th><th>Buku</th><th>Keterangan</th><th class="text-end">Jumlah</th></tr></thead>
    <tbody>
    <?php foreach ($item as $n => $i): ?>
        <tr><td><?= $n + 1 ?></td><td><?= e($i['jenis']) ?></td><td><?= e($i['judul']) ?> (<?= e($i['kode_eksemplar']) ?>)</td>
            <td><?= e($i['keterangan']) ?></td><td class="text-end"><?= rupiah((int) $i['jumlah']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="4">Total dibayar</td><td class="text-end"><?= rupiah($total) ?></td></tr></tfoot>
</table>
<div class="ttd">Penerima<br><br><br><br><?= e($h['penerima']) ?></div>
<?php require ROOT_DIR . '/includes/layout/print_footer.php'; ?>
