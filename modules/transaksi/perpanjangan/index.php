<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$petugas = user()['role'] === 'petugas';
$aktif = db_all(
    "SELECT d.id, d.tgl_jatuh_tempo, d.jumlah_perpanjangan, p.no_transaksi, a.nama, a.kode_anggota, e.kode_eksemplar, b.judul
     FROM peminjaman_detail d
     JOIN peminjaman p ON p.id = d.peminjaman_id
     JOIN anggota a ON a.id = p.anggota_id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     JOIN buku b ON b.id = e.buku_id
     WHERE d.status = 'dipinjam' ORDER BY d.tgl_jatuh_tempo"
);
$riwayat = db_all(
    'SELECT pr.*, a.nama, e.kode_eksemplar, b.judul, u.nama AS oleh, u.role
     FROM perpanjangan pr
     JOIN peminjaman_detail d ON d.id = pr.peminjaman_detail_id
     JOIN peminjaman p ON p.id = d.peminjaman_id
     JOIN anggota a ON a.id = p.anggota_id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     JOIN buku b ON b.id = e.buku_id
     JOIN users u ON u.id = pr.diajukan_oleh
     ORDER BY pr.tgl_perpanjangan DESC, pr.id DESC'
);

$judul = 'Perpanjangan';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-1">Perpanjangan</h1>
<p class="text-muted small">Maksimal <?= (int) setting('maks_perpanjangan') ?> kali per eksemplar, masing-masing +<?= (int) setting('lama_perpanjangan') ?> hari. Tidak bisa jika sudah terlambat, ada denda, atau judul direservasi anggota lain.</p>

<?php if ($petugas): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Pinjaman aktif</div>
    <div class="card-body">
    <table class="table table-sm align-middle datatable">
        <thead><tr><th>Anggota</th><th>Buku</th><th>Jatuh tempo</th><th>Diperpanjang</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($aktif as $x): ?>
            <?php $alasan = alasan_tidak_bisa_perpanjang($x['id'], user()['id']); ?>
            <tr>
                <td><?= e($x['nama']) ?> <div class="small text-muted font-monospace"><?= e($x['kode_anggota']) ?></div></td>
                <td><?= e($x['judul']) ?> <div class="small text-muted font-monospace"><?= e($x['kode_eksemplar']) ?> · <?= e($x['no_transaksi']) ?></div></td>
                <td data-order="<?= e($x['tgl_jatuh_tempo']) ?>" class="<?= $x['tgl_jatuh_tempo'] < hari_ini() ? 'text-danger fw-semibold' : '' ?>"><?= tgl($x['tgl_jatuh_tempo']) ?></td>
                <td><?= (int) $x['jumlah_perpanjangan'] ?>x</td>
                <td class="text-end">
                    <form method="post" action="<?= url('modules/transaksi/perpanjangan/proses.php') ?>" class="d-inline">
                        <?= csrf_field() ?><input type="hidden" name="detail_id" value="<?= $x['id'] ?>">
                        <button class="btn btn-sm btn-outline-primary" <?= $alasan ? 'disabled' : '' ?>><i class="bi bi-calendar-plus"></i> Perpanjang</button>
                    </form>
                    <?php if ($alasan): ?><div class="small text-muted"><?= e($alasan) ?></div><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Riwayat perpanjangan</div>
    <div class="card-body">
    <table class="table table-sm align-middle datatable">
        <thead><tr><th>Tanggal</th><th>Anggota</th><th>Buku</th><th>Jatuh tempo lama → baru</th><th>Diajukan oleh</th></tr></thead>
        <tbody>
        <?php foreach ($riwayat as $r): ?>
            <tr>
                <td data-order="<?= e($r['tgl_perpanjangan']) ?>"><?= tgl($r['tgl_perpanjangan']) ?></td>
                <td><?= e($r['nama']) ?></td>
                <td><?= e($r['judul']) ?> <span class="small text-muted font-monospace"><?= e($r['kode_eksemplar']) ?></span></td>
                <td><?= tgl($r['jatuh_tempo_lama']) ?> → <strong><?= tgl($r['jatuh_tempo_baru']) ?></strong></td>
                <td class="small"><?= e($r['oleh']) ?> <span class="badge text-bg-light"><?= e($r['role']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
