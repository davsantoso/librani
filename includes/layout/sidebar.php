<?php
// Dibungkus fungsi anonim agar variabel sidebar ($label, $path, ...) tidak menimpa variabel halaman.
(function (): void {
// Menu per role sesuai matriks hak akses PRD. Item hanya tampil jika halamannya sudah ada.
$menu = [
    ['Dashboard', 'bi-speedometer2', 'modules/dashboard/index.php', ['admin', 'petugas', 'anggota']],
    ['Katalog Buku', 'bi-grid-3x3-gap', 'modules/katalog/index.php', ['anggota']],
    ['Pinjaman Saya', 'bi-journal-bookmark', 'modules/anggota/pinjaman.php', ['anggota']],
    ['Denda Saya', 'bi-cash-coin', 'modules/anggota/denda.php', ['anggota']],
    ['Reservasi Saya', 'bi-bookmark-star', 'modules/anggota/reservasi.php', ['anggota']],
    'Transaksi',
    ['Peminjaman', 'bi-box-arrow-up-right', 'modules/transaksi/peminjaman/index.php', ['admin', 'petugas']],
    ['Pengembalian', 'bi-box-arrow-in-down-left', 'modules/transaksi/pengembalian/index.php', ['admin', 'petugas']],
    ['Denda', 'bi-cash-coin', 'modules/transaksi/denda/index.php', ['admin', 'petugas']],
    ['Perpanjangan', 'bi-calendar-plus', 'modules/transaksi/perpanjangan/index.php', ['admin', 'petugas']],
    ['Reservasi', 'bi-bookmark-star', 'modules/transaksi/reservasi/index.php', ['admin', 'petugas']],
    'Master',
    ['Buku', 'bi-book', 'modules/master/buku/index.php', ['admin', 'petugas']],
    ['Eksemplar', 'bi-upc-scan', 'modules/master/eksemplar/index.php', ['admin', 'petugas']],
    ['Anggota', 'bi-people', 'modules/master/anggota/index.php', ['admin', 'petugas']],
    ['Kategori', 'bi-tags', 'modules/master/kategori/index.php', ['admin', 'petugas']],
    ['Penerbit', 'bi-building', 'modules/master/penerbit/index.php', ['admin', 'petugas']],
    ['Rak', 'bi-bookshelf', 'modules/master/rak/index.php', ['admin', 'petugas']],
    ['User Petugas', 'bi-person-badge', 'modules/master/users/index.php', ['admin']],
    'Lainnya',
    ['Laporan', 'bi-file-earmark-bar-graph', 'modules/laporan/index.php', ['admin', 'petugas']],
    ['Pengaturan', 'bi-gear', 'modules/pengaturan/index.php', ['admin']],
];
$role = user()['role'];
// Akun anggota yang belum terverifikasi hanya melihat Dashboard dan Katalog (FR-REG-03).
$belum_aktif = $role === 'anggota' && !in_array(anggota_saya()['status'] ?? '', ['aktif', 'nonaktif'], true);
if ($belum_aktif) {
    $menu = array_values(array_filter($menu, fn($m) => is_array($m) && in_array($m[2], ['modules/dashboard/index.php', 'modules/katalog/index.php'], true)));
}
$sekarang = $_SERVER['SCRIPT_NAME'] ?? '';
$grup = null;
?>
<aside id="sidebar" class="offcanvas-lg offcanvas-start bg-dark text-white sidebar no-print" tabindex="-1">
    <div class="offcanvas-body p-0 d-block">
        <nav class="nav flex-column py-2">
        <?php foreach ($menu as $m):
            if (is_string($m)) {
                $grup = $m;
                continue;
            }
            [$label, $ikon, $path, $roles] = $m;
            if (!in_array($role, $roles, true) || !is_file(ROOT_DIR . '/' . $path)) {
                continue;
            }
            if ($grup) {
                echo '<div class="sidebar-grup">' . e($grup) . '</div>';
                $grup = null;
            }
            $aktif = str_contains($sekarang, '/' . dirname($path) . '/');
        ?>
            <a class="nav-link <?= $aktif ? 'active' : '' ?>" href="<?= url($path) ?>"><i class="bi <?= $ikon ?>"></i> <?= e($label) ?></a>
        <?php endforeach; ?>
        </nav>
    </div>
</aside>
<?php })(); ?>
