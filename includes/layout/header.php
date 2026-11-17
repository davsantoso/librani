<?php
/** @var string $judul  Judul halaman. Set $tanpa_sidebar = true untuk halaman login/publik. */
$tanpa_sidebar ??= !user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul ?? 'Librani') ?> · Librani</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body class="<?= $tanpa_sidebar ? 'polos' : 'dengan-sidebar' ?>">
<?php if (user()): ?>
<nav class="navbar navbar-expand bg-white border-bottom sticky-top px-3 no-print">
    <?php if (!$tanpa_sidebar): ?>
    <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Menu"><i class="bi bi-list"></i></button>
    <?php endif; ?>
    <a class="navbar-brand fw-semibold" href="<?= dashboard_url(user()['role']) ?>"><i class="bi bi-book-half text-primary"></i> Librani</a>
    <div class="ms-auto dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?= e(user()['nama']) ?>
            <span class="badge text-bg-secondary ms-1"><?= e(user()['role']) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <?php if (is_file(ROOT_DIR . '/modules/auth/profil.php')): ?>
            <li><a class="dropdown-item" href="<?= url('modules/auth/profil.php') ?>"><i class="bi bi-person"></i> Profil &amp; Password</a></li>
            <?php endif; ?>
            <li>
                <form method="post" action="<?= url('modules/auth/logout.php') ?>">
                    <?= csrf_field() ?>
                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Keluar</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
<?php endif; ?>
<div class="d-flex">
    <?php if (!$tanpa_sidebar) { require __DIR__ . '/sidebar.php'; } ?>
    <main class="flex-grow-1 p-3 p-lg-4 min-w-0">
