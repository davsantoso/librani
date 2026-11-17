<?php
require __DIR__ . '/../../includes/init.php';
$a = require_anggota_aktif();
cek_reservasi_kadaluarsa();
$data = reservasi_anggota($a['id']);
$label = ['menunggu' => ['info', 'Menunggu'], 'siap' => ['success', 'Siap diambil'], 'diambil' => ['secondary', 'Diambil'],
    'batal' => ['light', 'Dibatalkan'], 'kadaluarsa' => ['dark', 'Kadaluarsa']];

$judul = 'Reservasi Saya';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-1">Reservasi Saya</h1>
<p class="text-muted small">Reservasi hanya untuk judul yang semua eksemplarnya sedang dipinjam (lewat tombol di <a href="<?= url('modules/katalog/index.php') ?>">katalog</a>).
    Maksimal <?= (int) setting('maks_reservasi') ?> reservasi aktif; buku yang siap harus diambil dalam <?= (int) setting('batas_ambil_reservasi') ?> hari.</p>
<div class="card shadow-sm"><div class="card-body">
<table class="table align-middle datatable">
    <thead><tr><th>Buku</th><th>Tgl reservasi</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($data as $r): ?>
        <tr>
            <td><div class="d-flex gap-2 align-items-center"><div class="cover-kecil"><?= cover_html($r) ?></div>
                <div><div class="fw-semibold"><?= e($r['judul']) ?></div><div class="small text-muted"><?= e($r['penulis']) ?></div></div></div></td>
            <td data-order="<?= e($r['tgl_reservasi']) ?>"><?= tgl(substr($r['tgl_reservasi'], 0, 10)) ?></td>
            <td><span class="badge text-bg-<?= $label[$r['status']][0] ?>"><?= $label[$r['status']][1] ?></span>
                <?php if ($r['status'] === 'menunggu'): ?><div class="small">Antrean ke-<?= (int) $r['posisi'] ?></div><?php endif; ?>
                <?php if ($r['status'] === 'siap'): ?><div class="small">Ambil s.d. <strong><?= tgl($r['tgl_batas_ambil']) ?></strong> (<?= e($r['kode_eksemplar']) ?>)</div><?php endif; ?>
            </td>
            <td class="text-end">
                <?php if (in_array($r['status'], ['menunggu', 'siap'], true)): ?>
                    <form method="post" action="<?= url('modules/transaksi/reservasi/batal.php') ?>" class="konfirmasi" data-judul="Batalkan reservasi?" data-pesan="Posisi antrean Anda akan hilang.">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">Batalkan</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
