<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$data = db_all(
    "SELECT k.no_pengembalian, MAX(k.tgl_kembali) AS tgl_kembali, a.nama AS anggota, a.kode_anggota, u.nama AS petugas,
            COUNT(*) AS jumlah, SUM(k.hari_terlambat > 0) AS terlambat, SUM(k.kondisi <> 'baik') AS bermasalah,
            (SELECT COALESCE(SUM(dn.jumlah), 0) FROM denda dn JOIN pengembalian k2 ON k2.id = dn.pengembalian_id WHERE k2.no_pengembalian = k.no_pengembalian) AS denda
     FROM pengembalian k
     JOIN peminjaman_detail d ON d.id = k.peminjaman_detail_id
     JOIN peminjaman p ON p.id = d.peminjaman_id
     JOIN anggota a ON a.id = p.anggota_id
     JOIN users u ON u.id = k.petugas_id
     GROUP BY k.no_pengembalian, a.id, u.id
     ORDER BY tgl_kembali DESC, k.no_pengembalian DESC"
);

$judul = 'Pengembalian';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Riwayat Pengembalian</h1>
    <?php if (user()['role'] === 'petugas'): ?>
        <a class="btn btn-primary" href="<?= url('modules/transaksi/pengembalian/form.php') ?>"><i class="bi bi-box-arrow-in-down-left"></i> Proses pengembalian</a>
    <?php endif; ?>
</div>
<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover align-middle datatable">
    <thead><tr><th>No. pengembalian</th><th>Tanggal</th><th>Anggota</th><th class="text-end">Eksemplar</th><th>Keterangan</th><th class="text-end">Denda</th><th>Petugas</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($data as $k): ?>
        <tr>
            <td class="fw-semibold"><?= e($k['no_pengembalian']) ?></td>
            <td data-order="<?= e($k['tgl_kembali']) ?>"><?= tgl($k['tgl_kembali']) ?></td>
            <td><?= e($k['anggota']) ?> <div class="small text-muted font-monospace"><?= e($k['kode_anggota']) ?></div></td>
            <td class="text-end"><?= (int) $k['jumlah'] ?></td>
            <td>
                <?= $k['terlambat'] ? '<span class="badge text-bg-danger">Terlambat</span>' : '<span class="badge text-bg-success">Tepat waktu</span>' ?>
                <?= $k['bermasalah'] ? '<span class="badge text-bg-dark">Rusak/hilang</span>' : '' ?>
            </td>
            <td class="text-end" data-order="<?= (int) $k['denda'] ?>"><?= rupiah((int) $k['denda']) ?></td>
            <td class="small"><?= e($k['petugas']) ?></td>
            <td><a class="btn btn-sm btn-outline-secondary" href="<?= url('modules/transaksi/pengembalian/bukti.php?no=' . urlencode($k['no_pengembalian'])) ?>" target="_blank" title="Cetak bukti"><i class="bi bi-printer"></i></a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
