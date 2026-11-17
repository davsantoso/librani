<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

$data = db_all("SELECT * FROM users WHERE role IN ('admin','petugas') ORDER BY is_active DESC, role, nama");

$judul = 'User Petugas';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">User Petugas</h1>
    <a class="btn btn-primary" href="<?= url('modules/master/users/form.php') ?>"><i class="bi bi-plus-lg"></i> Tambah</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover align-middle datatable">
    <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($data as $r): ?>
        <tr class="<?= $r['is_active'] ? '' : 'text-muted' ?>">
            <td><?= e($r['nama']) ?><?= (int) $r['id'] === user()['id'] ? ' <span class="badge text-bg-info">Anda</span>' : '' ?></td>
            <td><?= e($r['username']) ?></td>
            <td><?= e($r['email']) ?></td>
            <td><span class="badge <?= $r['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-primary' ?>"><?= e($r['role']) ?></span></td>
            <td><?= $r['is_active'] ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= url('modules/master/users/form.php?id=' . $r['id']) ?>"><i class="bi bi-pencil"></i></a>
                <?php if ((int) $r['id'] !== user()['id'] && $r['is_active']): ?>
                <form method="post" action="<?= url('modules/master/users/hapus.php') ?>" class="d-inline konfirmasi"
                      data-pesan="User yang pernah memproses transaksi akan dinonaktifkan, bukan dihapus.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
