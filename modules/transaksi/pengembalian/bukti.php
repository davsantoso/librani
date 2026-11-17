<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$no = (string) ($_GET['no'] ?? '');
$item = db_all(
    "SELECT k.*, d.tgl_jatuh_tempo, p.no_transaksi, a.nama AS anggota, a.kode_anggota, e.kode_eksemplar, b.judul, u.nama AS petugas,
            (SELECT COALESCE(SUM(jumlah), 0) FROM denda WHERE pengembalian_id = k.id) AS denda
     FROM pengembalian k
     JOIN peminjaman_detail d ON d.id = k.peminjaman_detail_id
     JOIN peminjaman p ON p.id = d.peminjaman_id
     JOIN anggota a ON a.id = p.anggota_id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     JOIN buku b ON b.id = e.buku_id
     JOIN users u ON u.id = k.petugas_id
     WHERE k.no_pengembalian = ? ORDER BY k.id",
    [$no]
);
if (!$item) {
    http_response_code(404);
    exit('Pengembalian tidak ditemukan.');
}
$total = array_sum(array_column($item, 'denda'));
$h = $item[0];

$judul_cetak = 'BUKTI PENGEMBALIAN';
$sub_judul = $h['no_pengembalian'];
$kertas = 'A5';
$tanpa_cetak_otomatis = true;
$tombol_cetak = [
    '<a class="btn btn-outline-primary btn-sm" href="' . url('modules/transaksi/pengembalian/form.php') . '">Pengembalian berikutnya</a>',
];
if ($total > 0 && is_file(ROOT_DIR . '/modules/transaksi/denda/index.php')) {
    $tombol_cetak[] = '<a class="btn btn-warning btn-sm" href="' . url('modules/transaksi/denda/index.php?anggota=' . urlencode($h['kode_anggota'])) . '">Bayar denda</a>';
}
require ROOT_DIR . '/includes/layout/print_header.php';
?>
<table class="mb-3 small">
    <tr><td style="width:130px">No. pengembalian</td><td>: <strong><?= e($h['no_pengembalian']) ?></strong></td></tr>
    <tr><td>Tanggal kembali</td><td>: <?= tgl($h['tgl_kembali']) ?></td></tr>
    <tr><td>Anggota</td><td>: <?= e($h['anggota']) ?> (<?= e($h['kode_anggota']) ?>)</td></tr>
</table>
<table class="cetak">
    <thead><tr><th>Eksemplar</th><th>Judul</th><th>Jatuh tempo</th><th>Terlambat</th><th>Kondisi</th><th class="text-end">Denda</th></tr></thead>
    <tbody>
    <?php foreach ($item as $i): ?>
        <tr><td><?= e($i['kode_eksemplar']) ?></td><td><?= e($i['judul']) ?></td><td><?= tgl($i['tgl_jatuh_tempo']) ?></td>
            <td><?= (int) $i['hari_terlambat'] ?> hari</td><td><?= e($i['kondisi']) ?></td><td class="text-end"><?= rupiah((int) $i['denda']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="5">Total denda</td><td class="text-end"><?= rupiah($total) ?></td></tr></tfoot>
</table>
<?php if ($total > 0): ?><p class="small mt-2">Denda dibayar di meja layanan. Selama belum lunas, anggota belum bisa meminjam lagi.</p><?php endif; ?>
<div class="ttd">Petugas<br><br><br><br><?= e($h['petugas']) ?></div>
<?php require ROOT_DIR . '/includes/layout/print_footer.php'; ?>
