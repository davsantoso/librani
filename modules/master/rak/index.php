<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$admin = user()['role'] === 'admin';
$data = db_all(
    'SELECT r.*, (SELECT COUNT(*) FROM eksemplar e WHERE e.rak_id = r.id) AS jumlah_eksemplar
     FROM rak r ORDER BY r.is_active DESC, r.kode'
);

$judul = 'Rak';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Rak</h1>
    <?php if ($admin): ?>
        <a class="btn btn-primary" href="<?= url('modules/master/rak/form.php') ?>"><i class="bi bi-plus-lg"></i> Tambah</a>
    <?php endif; ?>
</div>
<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover align-middle datatable">
    <thead><tr><th>Kode</th><th>Nama</th><th>Lantai</th><th class="text-end">Eksemplar</th><th>Status</th><?php if ($admin): ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($data as $r): ?>
        <tr class="<?= $r['is_active'] ? '' : 'text-muted' ?>">
            <td><?= e($r['kode']) ?></td>
            <td><?= e($r['nama']) ?></td>
            <td><?= (int) $r['lantai'] ?></td>
            <td class="text-end"><?= (int) $r['jumlah_eksemplar'] ?></td>
            <td><?= $r['is_active'] ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' ?></td>
            <?php if ($admin): ?>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= url('modules/master/rak/form.php?id=' . $r['id']) ?>"><i class="bi bi-pencil"></i></a>
                <form method="post" action="<?= url('modules/master/rak/hapus.php') ?>" class="d-inline <?= $r['is_active'] ? 'konfirmasi' : '' ?>"
                      data-pesan="Rak yang sudah berisi eksemplar akan dinonaktifkan, bukan dihapus.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <?php if ($r['is_active']): ?>
                        <button class="btn btn-sm btn-outline-danger" name="aksi" value="hapus" title="Hapus"><i class="bi bi-trash"></i></button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline-success" name="aksi" value="aktifkan" title="Aktifkan kembali"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <?php endif; ?>
                </form>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
