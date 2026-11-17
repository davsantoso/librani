<?php
require __DIR__ . '/../../includes/init.php';

if (user()) {
    redirect(dashboard_url(user()['role']));
}

$error = null;
$login = '';
if (is_post()) {
    csrf_check();
    $login = post('login');
    $u = coba_login($login, (string) ($_POST['password'] ?? ''));
    if ($u) {
        login_user($u);
        redirect(dashboard_url($u['role']));
    }
    $error = 'Username/email atau password salah.';
}

$judul = 'Masuk';
$tanpa_sidebar = true;
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="auth-box">
    <div class="text-center mb-4">
        <a href="<?= url() ?>" class="text-decoration-none text-dark">
            <i class="bi bi-book-half text-primary display-5"></i>
            <h1 class="h3 mt-2 mb-0">Librani</h1>
        </a>
        <p class="text-muted"><?= e(setting('nama_perpustakaan')) ?></p>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Masuk</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="login">Username atau email</label>
                    <input class="form-control" id="login" name="login" value="<?= e($login) ?>" required autofocus autocomplete="username">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <button class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>
    <p class="text-center mt-3 small">
        <?php if (is_file(__DIR__ . '/register.php')): ?>
            Belum punya akun? <a href="<?= url('modules/auth/register.php') ?>">Daftar anggota</a> ·
        <?php endif; ?>
        <a href="<?= url() ?>">Lihat katalog</a>
    </p>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
