<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$data = db_all(
    "SELECT b.*, k.nama AS kategori, p.nama AS penerbit,
            COUNT(e.id) AS total,
            SUM(e.status = 'tersedia') AS tersedia
     FROM buku b
     JOIN kategori k ON k.id = b.kategori_id
     LEFT JOIN penerbit p ON p.id = b.penerbit_id
     LEFT JOIN eksemplar e ON e.buku_id = b.id
     GROUP BY b.id
     ORDER BY b.is_active DESC, b.judul"
);

$judul = 'Buku';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Buku</h1>
    <a class="btn btn-primary" href="<?= url('modules/master/buku/form.php') ?>"><i class="bi bi-plus-lg"></i> Tambah buku</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover align-middle datatable">
    <thead><tr><th>Sampul</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>ISBN</th><th class="text-end">Tersedia</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($data as $r): ?>
        <tr class="<?= $r['is_active'] ? '' : 'text-muted' ?>">
            <td><div class="cover-kecil"><?= cover_html($r) ?></div></td>
            <td><a href="<?= url('modules/master/buku/detail.php?id=' . $r['id']) ?>" class="fw-semibold text-decoration-none"><?= e($r['judul']) ?></a>
                <div class="small text-muted"><?= e($r['penerbit']) ?><?= $r['tahun_terbit'] ? ', ' . (int) $r['tahun_terbit'] : '' ?></div></td>
            <td><?= e($r['penulis']) ?></td>
            <td><?= e($r['kategori']) ?></td>
            <td class="small"><?= e($r['isbn']) ?></td>
            <td class="text-end" data-order="<?= (int) $r['tersedia'] ?>"><?= (int) $r['tersedia'] ?> / <?= (int) $r['total'] ?></td>
            <td><?= $r['is_active'] ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-secondary" href="<?= url('modules/master/buku/detail.php?id=' . $r['id']) ?>" title="Detail"><i class="bi bi-eye"></i></a>
                <a class="btn btn-sm btn-outline-primary" href="<?= url('modules/master/buku/form.php?id=' . $r['id']) ?>" title="Ubah"><i class="bi bi-pencil"></i></a>
                <form method="post" action="<?= url('modules/master/buku/hapus.php') ?>" class="d-inline <?= $r['is_active'] ? 'konfirmasi' : '' ?>"
                      data-pesan="Buku yang memiliki eksemplar akan dinonaktifkan (disembunyikan dari katalog), bukan dihapus.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <?php if ($r['is_active']): ?>
                        <button class="btn btn-sm btn-outline-danger" name="aksi" value="hapus" title="Hapus"><i class="bi bi-trash"></i></button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline-success" name="aksi" value="aktifkan" title="Aktifkan kembali"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
