<?php
/**
 * Grid katalog bergambar + form cari + paginasi. Dipakai /index.php (publik) dan modules/katalog/index.php (anggota).
 * Butuh: $aksi_form (URL halaman ini), $url_detail (URL detail tanpa id).
 */
cek_reservasi_kadaluarsa();   // tidak ada cron di hosting gratis: cek saat halaman dibuka
$q = trim((string) ($_GET['q'] ?? ''));
$kategori_id = (int) ($_GET['kategori'] ?? 0) ?: null;
$hal = max(1, (int) ($_GET['hal'] ?? 1));
$per_hal = 24;
$hasil = cari_katalog($q, $kategori_id, $hal, $per_hal);
$jumlah_hal = max(1, (int) ceil($hasil['total'] / $per_hal));
$daftar_kategori = db_all('SELECT id, nama FROM kategori WHERE is_active = 1 ORDER BY nama');
$link_hal = fn(int $h) => e($aksi_form . '?' . http_build_query(['q' => $q, 'kategori' => $kategori_id, 'hal' => $h]));
?>
<form class="row g-2 mb-4" method="get" action="<?= e($aksi_form) ?>" role="search">
    <div class="col-md-7">
        <input class="form-control form-control-lg" name="q" value="<?= e($q) ?>" placeholder="Cari judul, penulis, ISBN, atau kategori" aria-label="Kata kunci">
    </div>
    <div class="col-md-3">
        <select class="form-select form-select-lg" name="kategori" aria-label="Kategori">
            <option value="">Semua kategori</option>
            <?php foreach ($daftar_kategori as $k): ?>
                <option value="<?= $k['id'] ?>" <?= $kategori_id === $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary btn-lg w-100"><i class="bi bi-search"></i> Cari</button></div>
</form>

<p class="text-muted small"><?= $hasil['total'] ?> judul ditemukan<?= $q !== '' ? ' untuk “' . e($q) . '”' : '' ?>.</p>

<?php if (!$hasil['data']): ?>
    <div class="text-center text-muted py-5"><i class="bi bi-journal-x display-4"></i><p class="mt-2">Buku tidak ditemukan. Coba kata kunci lain.</p></div>
<?php endif; ?>

<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3">
    <?php foreach ($hasil['data'] as $b): ?>
        <div class="col">
            <a class="card h-100 shadow-sm text-decoration-none text-dark kartu-buku" href="<?= e($url_detail . '?id=' . $b['id']) ?>">
                <?= cover_html($b) ?>
                <div class="card-body p-2">
                    <div class="fw-semibold small lh-sm judul-buku"><?= e($b['judul']) ?></div>
                    <div class="text-muted small text-truncate"><?= e($b['penulis']) ?></div>
                    <?php if ($b['tersedia'] > 0): ?>
                        <span class="badge text-bg-success mt-1"><?= (int) $b['tersedia'] ?> tersedia</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary mt-1">Sedang dipinjam</span>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($jumlah_hal > 1): ?>
<nav class="mt-4" aria-label="Halaman katalog">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $hal <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $link_hal($hal - 1) ?>">‹</a></li>
        <?php for ($i = 1; $i <= $jumlah_hal; $i++): ?>
            <li class="page-item <?= $i === $hal ? 'active' : '' ?>"><a class="page-link" href="<?= $link_hal($i) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= $hal >= $jumlah_hal ? 'disabled' : '' ?>"><a class="page-link" href="<?= $link_hal($hal + 1) ?>">›</a></li>
    </ul>
</nav>
<?php endif; ?>
