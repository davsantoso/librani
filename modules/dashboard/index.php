<?php
require __DIR__ . '/../../includes/init.php';
require_login();

$anggota = user()['role'] === 'anggota' ? anggota_saya() : null;

$judul = 'Dashboard';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3">Dashboard</h1>
<p>Selamat datang, <strong><?= e(user()['nama']) ?></strong>.</p>

<?php if ($anggota && $anggota['status'] !== 'aktif'): ?>
    <?php if ($anggota['status'] === 'menunggu'): ?>
        <div class="alert alert-warning">
            <h2 class="h5"><i class="bi bi-hourglass-split"></i> Akun menunggu verifikasi</h2>
            <p class="mb-0">Datang ke meja layanan dengan membawa kartu identitas (no. <?= e($anggota['no_identitas']) ?>).
                Sambil menunggu, Anda bisa melihat <a href="<?= url() ?>">katalog buku</a>.</p>
        </div>
    <?php elseif ($anggota['status'] === 'ditolak'): ?>
        <div class="alert alert-danger">
            <h2 class="h5"><i class="bi bi-x-circle"></i> Pendaftaran ditolak</h2>
            <p class="mb-0">Alasan: <?= e($anggota['alasan_tolak']) ?></p>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary">Keanggotaan Anda nonaktif. Hubungi petugas untuk mengaktifkan kembali.</div>
    <?php endif; ?>
<?php endif; ?>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
