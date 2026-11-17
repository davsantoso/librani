<?php
// Katalog untuk anggota yang sudah login (semua status, termasuk menunggu verifikasi).
require __DIR__ . '/../../includes/init.php';
require_role('anggota');

$judul = 'Katalog Buku';
$aksi_form = url('modules/katalog/index.php');
$url_detail = url('modules/katalog/detail.php');
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3">Katalog Buku</h1>
<?php require ROOT_DIR . '/includes/layout/katalog_grid.php'; ?>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
