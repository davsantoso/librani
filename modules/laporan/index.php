<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin', 'petugas');

$laporan = [
    ['peminjaman.php', 'Laporan Peminjaman', 'Transaksi pinjam per eksemplar, filter periode/anggota/kategori/status.', 'bi-box-arrow-up-right'],
    ['pengembalian.php', 'Laporan Pengembalian', 'Pengembalian, hari terlambat, kondisi, dan denda.', 'bi-box-arrow-in-down-left'],
    ['denda.php', 'Laporan Denda', 'Denda per anggota dengan total lunas dan belum lunas.', 'bi-cash-coin'],
    ['keterlambatan.php', 'Laporan Keterlambatan Aktif', 'Pinjaman yang lewat jatuh tempo per hari ini dan estimasi dendanya.', 'bi-exclamation-triangle'],
    ['populer.php', 'Rekap Buku Terpopuler & Anggota Teraktif', '10 judul paling sering dipinjam dan 10 anggota paling aktif.', 'bi-trophy'],
    ['koleksi.php', 'Laporan Koleksi', 'Jumlah eksemplar per status untuk setiap judul.', 'bi-collection'],
    ['anggota.php', 'Laporan Anggota', 'Daftar anggota, masa berlaku, status, dan jumlah pinjaman.', 'bi-people'],
];

$judul = 'Laporan';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3">Laporan</h1>
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
    <?php foreach ($laporan as [$file, $nama, $ket, $ikon]): ?>
        <div class="col">
            <a class="card shadow-sm h-100 text-decoration-none text-reset" href="<?= url('modules/laporan/' . $file) ?>">
                <div class="card-body">
                    <div class="fs-3 text-primary"><i class="bi <?= $ikon ?>"></i></div>
                    <div class="fw-semibold"><?= e($nama) ?></div>
                    <div class="small text-muted"><?= e($ket) ?></div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
