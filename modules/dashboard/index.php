<?php
require __DIR__ . '/../../includes/init.php';
require_login();

$judul = 'Dashboard';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3">Dashboard</h1>
<p>Selamat datang, <strong><?= e(user()['nama']) ?></strong>.</p>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
