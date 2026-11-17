<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$f = [
    'buku_id' => (int) ($_GET['buku_id'] ?? 0),
    'rak_id' => (int) ($_GET['rak_id'] ?? 0),
    'status' => in_array($_GET['status'] ?? '', ['tersedia', 'dipinjam', 'direservasi', 'rusak', 'hilang'], true) ? $_GET['status'] : '',
];
$where = ['1 = 1'];
$params = [];
foreach (['buku_id' => 'e.buku_id', 'rak_id' => 'e.rak_id', 'status' => 'e.status'] as $k => $kolom) {
    if ($f[$k]) {
        $where[] = "$kolom = ?";
        $params[] = $f[$k];
    }
}
$data = db_all(
    'SELECT e.*, b.judul, b.penulis, b.foto_thumb, b.foto_cover, r.kode AS rak
     FROM eksemplar e JOIN buku b ON b.id = e.buku_id LEFT JOIN rak r ON r.id = e.rak_id
     WHERE ' . implode(' AND ', $where) . ' ORDER BY e.kode_eksemplar',
    $params
);
$buku = db_all('SELECT id, judul FROM buku ORDER BY judul');
$rak = db_all('SELECT id, kode FROM rak ORDER BY kode');
$warna = ['tersedia' => 'success', 'dipinjam' => 'warning', 'direservasi' => 'info', 'rusak' => 'danger', 'hilang' => 'dark'];

$judul = 'Eksemplar';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Eksemplar</h1>
    <a class="btn btn-primary" href="<?= url('modules/master/eksemplar/form.php' . ($f['buku_id'] ? '?buku_id=' . $f['buku_id'] : '')) ?>"><i class="bi bi-plus-lg"></i> Tambah eksemplar</a>
</div>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-5">
        <select class="form-select" name="buku_id" aria-label="Buku">
            <option value="">Semua buku</option>
            <?php foreach ($buku as $b): ?><option value="<?= $b['id'] ?>" <?= $f['buku_id'] === $b['id'] ? 'selected' : '' ?>><?= e($b['judul']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" name="rak_id" aria-label="Rak">
            <option value="">Semua rak</option>
            <?php foreach ($rak as $r): ?><option value="<?= $r['id'] ?>" <?= $f['rak_id'] === $r['id'] ? 'selected' : '' ?>><?= e($r['kode']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" name="status" aria-label="Status">
            <option value="">Semua status</option>
            <?php foreach (array_keys($warna) as $s): ?><option value="<?= $s ?>" <?= $f['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Saring</button></div>
</form>
<div class="card shadow-sm"><div class="card-body">
<table class="table table-hover align-middle datatable">
    <thead><tr><th>Kode</th><th>Buku</th><th>Rak</th><th>Kondisi</th><th>Status</th><th>Pengadaan</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($data as $x): ?>
        <tr>
            <td class="font-monospace"><?= e($x['kode_eksemplar']) ?></td>
            <td><div class="d-flex gap-2 align-items-center"><div class="cover-kecil" style="width:32px"><?= cover_html($x) ?></div>
                <a class="text-decoration-none" href="<?= url('modules/master/buku/detail.php?id=' . $x['buku_id']) ?>"><?= e($x['judul']) ?></a></div></td>
            <td><?= e($x['rak'] ?? '-') ?></td>
            <td><?= e($x['kondisi']) ?></td>
            <td><span class="badge text-bg-<?= $warna[$x['status']] ?>"><?= e($x['status']) ?></span></td>
            <td data-order="<?= e($x['tgl_pengadaan']) ?>"><?= tgl($x['tgl_pengadaan']) ?> <span class="small text-muted">(<?= e($x['sumber']) ?>)</span></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= url('modules/master/eksemplar/form.php?id=' . $x['id']) ?>" title="Ubah"><i class="bi bi-pencil"></i></a>
                <?php if (!in_array($x['status'], ['dipinjam', 'direservasi'], true)): ?>
                <form method="post" action="<?= url('modules/master/eksemplar/tandai.php') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $x['id'] ?>">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Tandai">Tandai</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach (['tersedia', 'rusak', 'hilang'] as $s): ?>
                                <?php if ($s !== $x['status']): ?><li><button class="dropdown-item" name="status" value="<?= $s ?>"><?= ucfirst($s) ?></button></li><?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </form>
                <form method="post" action="<?= url('modules/master/eksemplar/hapus.php') ?>" class="d-inline konfirmasi" data-pesan="Eksemplar yang punya riwayat transaksi tidak bisa dihapus.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $x['id'] ?>">
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
