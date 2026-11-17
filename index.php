<?php
require __DIR__ . '/includes/init.php';

$judul = 'Beranda';
$tanpa_sidebar = true;
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="text-center py-5">
    <i class="bi bi-book-half text-primary display-3"></i>
    <h1 class="mt-2">Librani</h1>
    <p class="text-muted"><?= e(setting('nama_perpustakaan')) ?></p>
    <a class="btn btn-primary" href="<?= url('modules/auth/login.php') ?>">Masuk</a>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
