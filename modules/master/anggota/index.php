<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$status = in_array($_GET['status'] ?? '', ['menunggu', 'aktif', 'ditolak', 'nonaktif'], true) ? $_GET['status'] : '';
$data = db_all(
    "SELECT a.*, u.email, u.username,
            (SELECT COUNT(*) FROM peminjaman p WHERE p.anggota_id = a.id) AS jumlah_pinjam
     FROM anggota a JOIN users u ON u.id = a.user_id
     WHERE (? = '' OR a.status = ?)
     ORDER BY a.status = 'menunggu' DESC, a.tgl_daftar DESC, a.nama",
    [$status, $status]
);
$jumlah = array_column(db_all('SELECT status, COUNT(*) AS n FROM anggota GROUP BY status'), 'n', 'status');
$warna = ['menunggu' => 'warning', 'aktif' => 'success', 'ditolak' => 'danger', 'nonaktif' => 'secondary'];

$judul = 'Anggota';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Anggota</h1>
    <a class="btn btn-primary" href="<?= url('modules/master/anggota/form.php') ?>"><i class="bi bi-person-plus"></i> Tambah anggota</a>
</div>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link <?= $status === '' ? 'active' : '' ?>" href="?">Semua</a></li>
    <?php foreach ($warna as $s => $w): ?>
        <li class="nav-item"><a class="nav-link <?= $status === $s ? 'active' : '' ?>" href="?status=<?= $s ?>"><?= ucfirst($s) ?>
            <span class="badge text-bg-<?= $w ?>"><?= (int) ($jumlah[$s] ?? 0) ?></span></a></li>
    <?php endforeach; ?>
</ul>
<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover align-middle datatable">
    <thead><tr><th>Kode</th><th>Nama</th><th>No. identitas</th><th>No. HP</th><th>Daftar</th><th>Berlaku s.d.</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($data as $a): ?>
        <?php $kedaluwarsa = $a['status'] === 'aktif' && $a['tgl_berlaku'] < hari_ini(); ?>
        <tr>
            <td class="font-monospace"><?= e($a['kode_anggota'] ?? '-') ?></td>
            <td><a class="text-decoration-none fw-semibold" href="<?= url('modules/master/anggota/detail.php?id=' . $a['id']) ?>"><?= e($a['nama']) ?></a>
                <div class="small text-muted"><?= e($a['email']) ?></div></td>
            <td><?= e($a['no_identitas']) ?></td>
            <td><?= e($a['no_hp']) ?></td>
            <td data-order="<?= e($a['tgl_daftar']) ?>"><?= tgl($a['tgl_daftar']) ?></td>
            <td data-order="<?= e((string) $a['tgl_berlaku']) ?>" class="<?= $kedaluwarsa ? 'text-danger fw-semibold' : '' ?>"><?= tgl($a['tgl_berlaku']) ?></td>
            <td><span class="badge text-bg-<?= $warna[$a['status']] ?>"><?= e($a['status']) ?></span><?= $kedaluwarsa ? ' <span class="badge text-bg-danger">kedaluwarsa</span>' : '' ?></td>
            <td class="text-end text-nowrap">
                <?php if ($a['status'] === 'menunggu'): ?>
                    <a class="btn btn-sm btn-warning" href="<?= url('modules/master/anggota/detail.php?id=' . $a['id']) ?>"><i class="bi bi-person-check"></i> Verifikasi</a>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('modules/master/anggota/detail.php?id=' . $a['id']) ?>" title="Detail"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-sm btn-outline-primary" href="<?= url('modules/master/anggota/form.php?id=' . $a['id']) ?>" title="Ubah"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
