<?php
// Bisa dibuka langsung atau di-require oleh require_role() saat role tidak sesuai.
require_once __DIR__ . '/../../includes/init.php';
http_response_code(403);

$judul = '403 — Akses ditolak';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="text-center py-5">
    <i class="bi bi-shield-lock display-1 text-danger"></i>
    <h1 class="h3 mt-3">403 — Akses ditolak</h1>
    <p class="text-muted">Anda tidak memiliki hak untuk membuka halaman ini.</p>
    <a class="btn btn-primary" href="<?= user() ? dashboard_url(user()['role']) : url() ?>">Kembali</a>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
