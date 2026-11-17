<?php
// Detail buku untuk publik dan anggota: data buku + status eksemplar (tanpa info peminjam).
require __DIR__ . '/../../includes/init.php';
cek_reservasi_kadaluarsa();

$id = (int) ($_GET['id'] ?? 0);
$b = db_row(
    'SELECT b.*, k.nama AS kategori, p.nama AS penerbit
     FROM buku b JOIN kategori k ON k.id = b.kategori_id LEFT JOIN penerbit p ON p.id = b.penerbit_id
     WHERE b.id = ? AND b.is_active = 1',
    [$id]
);
if (!$b) {
    http_response_code(404);
}
$eksemplar = $b ? db_all(
    "SELECT e.kode_eksemplar, e.status, r.kode AS rak FROM eksemplar e LEFT JOIN rak r ON r.id = e.rak_id
     WHERE e.buku_id = ? AND e.status NOT IN ('rusak','hilang') ORDER BY e.kode_eksemplar",
    [$id]
) : [];
$tersedia = count(array_filter($eksemplar, fn($x) => $x['status'] === 'tersedia'));
$label = ['tersedia' => ['success', 'Tersedia'], 'dipinjam' => ['warning', 'Dipinjam'], 'direservasi' => ['info', 'Direservasi']];
$kembali = user() ? url('modules/katalog/index.php') : url();

$judul = $b['judul'] ?? 'Buku tidak ditemukan';
$tanpa_sidebar = !user() || user()['role'] !== 'anggota';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="<?= $tanpa_sidebar ? 'container py-4' : '' ?>">
    <a class="btn btn-sm btn-link px-0 mb-2" href="<?= $kembali ?>"><i class="bi bi-arrow-left"></i> Kembali ke katalog</a>
    <?php if (!$b): ?>
        <div class="alert alert-warning">Buku tidak ditemukan.</div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-4 col-lg-3"><div style="max-width: 260px"><?= cover_html($b, 'besar') ?></div></div>
        <div class="col-md-8 col-lg-9">
            <h1 class="h3 mb-1"><?= e($b['judul']) ?></h1>
            <p class="text-muted"><?= e($b['penulis']) ?></p>
            <p>
                <?php if ($tersedia): ?>
                    <span class="badge text-bg-success fs-6"><?= $tersedia ?> eksemplar tersedia</span>
                <?php else: ?>
                    <span class="badge text-bg-secondary fs-6">Semua eksemplar sedang dipinjam</span>
                <?php endif; ?>
            </p>
            <?php require ROOT_DIR . '/includes/layout/tombol_reservasi.php'; ?>
            <dl class="row small">
                <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9"><?= e($b['kategori']) ?></dd>
                <dt class="col-sm-3">Penerbit</dt><dd class="col-sm-9"><?= e($b['penerbit'] ?? '-') ?></dd>
                <dt class="col-sm-3">Tahun terbit</dt><dd class="col-sm-9"><?= e((string) ($b['tahun_terbit'] ?? '-')) ?></dd>
                <dt class="col-sm-3">Halaman</dt><dd class="col-sm-9"><?= e((string) ($b['halaman'] ?? '-')) ?></dd>
                <dt class="col-sm-3">ISBN</dt><dd class="col-sm-9"><?= e($b['isbn'] ?? '-') ?></dd>
            </dl>
            <?php if ($b['sinopsis']): ?><h2 class="h6">Sinopsis</h2><p><?= nl2br(e($b['sinopsis'])) ?></p><?php endif; ?>
            <h2 class="h6 mt-4">Eksemplar</h2>
            <table class="table table-sm" style="max-width: 480px">
                <thead><tr><th>Kode</th><th>Rak</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($eksemplar as $x): ?>
                    <tr><td class="font-monospace"><?= e($x['kode_eksemplar']) ?></td><td><?= e($x['rak'] ?? '-') ?></td>
                        <td><span class="badge text-bg-<?= $label[$x['status']][0] ?>"><?= $label[$x['status']][1] ?></span></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
