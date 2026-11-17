<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$filter = in_array($_GET['status'] ?? '', ['aktif', 'selesai', 'terlambat'], true) ? $_GET['status'] : '';
$hari_ini = hari_ini();
$data = db_all(
    "SELECT p.*, a.nama AS anggota, a.kode_anggota, u.nama AS petugas,
            COUNT(d.id) AS jumlah,
            SUM(d.status = 'dipinjam') AS belum_kembali,
            SUM(d.status = 'dipinjam' AND d.tgl_jatuh_tempo < ?) AS terlambat,
            MIN(d.tgl_jatuh_tempo) AS jatuh_tempo
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN users u ON u.id = p.petugas_id
     JOIN peminjaman_detail d ON d.peminjaman_id = p.id
     WHERE (? = '' OR (? = 'terlambat' AND p.status = 'aktif') OR p.status = ?)
     GROUP BY p.id
     HAVING (? <> 'terlambat' OR terlambat > 0)
     ORDER BY p.tgl_pinjam DESC, p.id DESC",
    [$hari_ini, $filter, $filter, $filter, $filter]
);

$judul = 'Peminjaman';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Peminjaman</h1>
    <?php if (user()['role'] === 'petugas'): ?>
        <a class="btn btn-primary" href="<?= url('modules/transaksi/peminjaman/form.php') ?>"><i class="bi bi-plus-lg"></i> Peminjaman baru</a>
    <?php endif; ?>
</div>
<ul class="nav nav-pills mb-3">
    <?php foreach (['' => 'Semua', 'aktif' => 'Aktif', 'terlambat' => 'Terlambat', 'selesai' => 'Selesai'] as $k => $l): ?>
        <li class="nav-item"><a class="nav-link <?= $filter === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $l ?></a></li>
    <?php endforeach; ?>
</ul>
<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover align-middle datatable">
    <thead><tr><th>No. transaksi</th><th>Tanggal</th><th>Anggota</th><th class="text-end">Eksemplar</th><th>Jatuh tempo</th><th>Status</th><th>Petugas</th></tr></thead>
    <tbody>
    <?php foreach ($data as $p): ?>
        <tr>
            <td><a class="text-decoration-none fw-semibold" href="<?= url('modules/transaksi/peminjaman/detail.php?id=' . $p['id']) ?>"><?= e($p['no_transaksi']) ?></a></td>
            <td data-order="<?= e($p['tgl_pinjam']) ?>"><?= tgl($p['tgl_pinjam']) ?></td>
            <td><?= e($p['anggota']) ?> <div class="small text-muted font-monospace"><?= e($p['kode_anggota']) ?></div></td>
            <td class="text-end"><?= (int) $p['jumlah'] ?><?= $p['belum_kembali'] && $p['belum_kembali'] < $p['jumlah'] ? ' <span class="small text-muted">(' . (int) $p['belum_kembali'] . ' belum kembali)</span>' : '' ?></td>
            <td data-order="<?= e($p['jatuh_tempo']) ?>"><?= tgl($p['jatuh_tempo']) ?></td>
            <td>
                <?php if ($p['status'] === 'selesai'): ?><span class="badge text-bg-secondary">Selesai</span>
                <?php elseif ($p['terlambat']): ?><span class="badge text-bg-danger">Terlambat</span>
                <?php else: ?><span class="badge text-bg-warning">Aktif</span><?php endif; ?>
            </td>
            <td class="small"><?= e($p['petugas']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
