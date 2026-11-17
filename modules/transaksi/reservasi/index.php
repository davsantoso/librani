<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

cek_reservasi_kadaluarsa();
$status = in_array($_GET['status'] ?? 'aktif', ['aktif', 'siap', 'menunggu', 'diambil', 'batal', 'kadaluarsa', 'semua'], true) ? ($_GET['status'] ?? 'aktif') : 'aktif';
$data = db_all(
    "SELECT r.*, a.nama, a.kode_anggota, b.judul, e.kode_eksemplar
     FROM reservasi r JOIN anggota a ON a.id = r.anggota_id JOIN buku b ON b.id = r.buku_id LEFT JOIN eksemplar e ON e.id = r.eksemplar_id
     WHERE ? = 'semua' OR (? = 'aktif' AND r.status IN ('menunggu','siap')) OR r.status = ?
     ORDER BY r.status = 'siap' DESC, r.tgl_reservasi",
    [$status, $status, $status]
);
$label = ['menunggu' => 'info', 'siap' => 'success', 'diambil' => 'secondary', 'batal' => 'light', 'kadaluarsa' => 'dark'];

$judul = 'Reservasi';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-1">Reservasi</h1>
<p class="text-muted small">Buku berstatus <strong>siap</strong> diserahkan lewat form peminjaman biasa; eksemplar yang dikunci hanya bisa dipinjam oleh pemesannya.
    Reservasi yang lewat batas ambil otomatis kadaluarsa saat halaman dibuka.</p>
<ul class="nav nav-pills mb-3">
    <?php foreach (['aktif' => 'Aktif', 'siap' => 'Siap diambil', 'menunggu' => 'Menunggu', 'diambil' => 'Diambil', 'kadaluarsa' => 'Kadaluarsa', 'batal' => 'Batal', 'semua' => 'Semua'] as $k => $l): ?>
        <li class="nav-item"><a class="nav-link <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $l ?></a></li>
    <?php endforeach; ?>
</ul>
<div class="card shadow-sm"><div class="card-body">
<table class="table align-middle datatable">
    <thead><tr><th>Tgl reservasi</th><th>Anggota</th><th>Buku</th><th>Status</th><th>Batas ambil</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($data as $r): ?>
        <tr>
            <td data-order="<?= e($r['tgl_reservasi']) ?>"><?= tgl(substr($r['tgl_reservasi'], 0, 10)) ?> <span class="small text-muted"><?= substr($r['tgl_reservasi'], 11, 5) ?></span></td>
            <td><?= e($r['nama']) ?> <div class="small text-muted font-monospace"><?= e($r['kode_anggota']) ?></div></td>
            <td><?= e($r['judul']) ?><?= $r['kode_eksemplar'] ? ' <div class="small font-monospace">' . e($r['kode_eksemplar']) . '</div>' : '' ?></td>
            <td><span class="badge text-bg-<?= $label[$r['status']] ?>"><?= e($r['status']) ?></span></td>
            <td><?= tgl($r['tgl_batas_ambil']) ?></td>
            <td class="text-end">
                <?php if ($r['status'] === 'siap' && user()['role'] === 'petugas'): ?>
                    <a class="btn btn-sm btn-success" href="<?= url('modules/transaksi/peminjaman/form.php?anggota=' . urlencode($r['kode_anggota'])) ?>"><i class="bi bi-box-arrow-up-right"></i> Serahkan</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
