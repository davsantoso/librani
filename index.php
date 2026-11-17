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
<section class="hero text-white py-5 mb-4">
    <div class="container">
        <h1 class="display-6 fw-semibold mb-1"><?= e(setting('nama_perpustakaan')) ?></h1>
        <p class="lead mb-0">Cari buku, cek ketersediaan, lalu pinjam di meja layanan.</p>
    </div>
</section>
<div class="container pb-5">
    <?php require ROOT_DIR . '/includes/layout/katalog_grid.php'; ?>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
