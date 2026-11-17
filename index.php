<?php
// Landing page + katalog publik.
require __DIR__ . '/includes/init.php';

$judul = 'Katalog';
$tanpa_sidebar = true;
$aksi_form = url();
$url_detail = url('modules/katalog/detail.php');
require ROOT_DIR . '/includes/layout/header.php';
?>
<?php if (!user()): ?>
<nav class="navbar bg-white border-bottom px-3 mb-0">
    <a class="navbar-brand fw-semibold" href="<?= url() ?>"><i class="bi bi-book-half text-primary"></i> Librani</a>
    <div>
        <a class="btn btn-sm btn-outline-primary" href="<?= url('modules/auth/register.php') ?>">Daftar</a>
        <a class="btn btn-sm btn-primary" href="<?= url('modules/auth/login.php') ?>">Masuk</a>
    </div>
</nav>
<?php endif; ?>
<?php
$angka = [
    // Hanya buku aktif, sama dengan yang tampil di katalog.
    'Judul buku' => (int) db_val('SELECT COUNT(*) FROM buku WHERE is_active = 1'),
    'Eksemplar tersedia' => (int) db_val("SELECT COUNT(*) FROM eksemplar e JOIN buku b ON b.id = e.buku_id WHERE e.status = 'tersedia' AND b.is_active = 1"),
    'Kategori' => (int) db_val('SELECT COUNT(*) FROM kategori WHERE is_active = 1'),
];
// Rak di hero: lima buku aktif, yang bersampul foto didahulukan.
$rak = db_all('SELECT id, judul, penulis, foto_thumb, foto_cover FROM buku WHERE is_active = 1 ORDER BY foto_thumb IS NULL, id DESC LIMIT 5');
?>
<section class="beranda-hero mb-4">
    <div class="container">
        <div class="row align-items-end g-5">
            <div class="col-lg-6" data-enter>
                <p class="eyebrow mb-3">Katalog daring</p>
                <h1 class="mb-3"><?= e(setting('nama_perpustakaan')) ?></h1>
                <p class="lead mb-0">Cari buku, cek ketersediaan, lalu pinjam di meja layanan.</p>
                <dl class="beranda-angka">
                    <?php foreach ($angka as $label => $n): ?>
                        <div><dt><?= e($label) ?></dt><dd><?= number_format($n, 0, ',', '.') ?></dd></div>
                    <?php endforeach; ?>
                </dl>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-primary btn-lg" href="#katalog"><i class="bi bi-search"></i> Cari buku</a>
                    <?php if (!user()): ?>
                        <a class="btn btn-outline-secondary btn-lg" href="<?= url('modules/auth/register.php') ?>">Daftar anggota</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (count($rak) === 5): ?>
            <div class="col-lg-6 d-none d-lg-block" data-enter="2">
                <div class="rak">
                    <?php foreach ($rak as $b): ?>
                        <a href="<?= e(url('modules/katalog/detail.php?id=' . $b['id'])) ?>" title="<?= e($b['judul']) ?>"><?= cover_html($b) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<div class="container pb-5" id="katalog">
    <?php require ROOT_DIR . '/includes/layout/katalog_grid.php'; ?>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
