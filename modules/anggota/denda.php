<?php
require __DIR__ . '/../../includes/init.php';
$a = require_anggota_aktif();
$data = db_all(
    "SELECT dn.*, k.tgl_kembali, k.hari_terlambat, b.judul, b.foto_thumb, b.foto_cover, e.kode_eksemplar
     FROM denda dn
     JOIN pengembalian k ON k.id = dn.pengembalian_id
     JOIN peminjaman_detail d ON d.id = k.peminjaman_detail_id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     JOIN buku b ON b.id = e.buku_id
     WHERE dn.anggota_id = ? ORDER BY dn.status = 'belum_lunas' DESC, k.tgl_kembali DESC",
    [$a['id']]
);
$belum = array_sum(array_map(fn($d) => $d['status'] === 'belum_lunas' ? (int) $d['jumlah'] : 0, $data));
$warna = ['belum_lunas' => 'danger', 'lunas' => 'success', 'dibebaskan' => 'secondary'];

$judul = 'Denda Saya';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-1">Denda Saya</h1>
<p class="<?= $belum ? 'text-danger fw-semibold' : 'text-success' ?>">
    <?= $belum ? 'Total belum lunas ' . rupiah($belum) . '. Bayar di meja layanan agar bisa meminjam lagi.' : 'Tidak ada denda yang belum lunas.' ?>
</p>
<div class="card shadow-sm"><div class="card-body">
<table class="table align-middle datatable">
    <thead><tr><th>Buku</th><th>Jenis</th><th>Keterangan</th><th class="text-end">Jumlah</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($data as $d): ?>
        <tr>
            <td><div class="d-flex gap-2 align-items-center"><div class="cover-kecil" style="width:36px"><?= cover_html($d) ?></div>
                <div><?= e($d['judul']) ?><div class="small text-muted">Kembali <?= tgl($d['tgl_kembali']) ?></div></div></div></td>
            <td><?= e($d['jenis']) ?></td>
            <td class="small"><?= e($d['keterangan']) ?></td>
            <td class="text-end"><?= rupiah((int) $d['jumlah']) ?></td>
            <td><span class="badge text-bg-<?= $warna[$d['status']] ?>"><?= e(str_replace('_', ' ', $d['status'])) ?></span>
                <?php if ($d['tgl_bayar']): ?><div class="small text-muted"><?= tgl($d['tgl_bayar']) ?></div><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
