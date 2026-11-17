<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$id = (int) ($_GET['id'] ?? 0);
$b = db_row(
    'SELECT b.*, k.nama AS kategori, p.nama AS penerbit, p.kota
     FROM buku b JOIN kategori k ON k.id = b.kategori_id LEFT JOIN penerbit p ON p.id = b.penerbit_id
     WHERE b.id = ?',
    [$id]
);
if (!$b) {
    flash('gagal', 'Buku tidak ditemukan.');
    redirect('modules/master/buku/index.php');
}
$eksemplar = db_all(
    'SELECT e.*, r.kode AS rak FROM eksemplar e LEFT JOIN rak r ON r.id = e.rak_id WHERE e.buku_id = ? ORDER BY e.kode_eksemplar',
    [$id]
);
$riwayat = db_all(
    "SELECT p.no_transaksi, p.tgl_pinjam, a.nama AS anggota, a.kode_anggota, e.kode_eksemplar, d.tgl_jatuh_tempo, d.status,
            k.tgl_kembali, k.kondisi
     FROM peminjaman_detail d
     JOIN peminjaman p ON p.id = d.peminjaman_id
     JOIN anggota a ON a.id = p.anggota_id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     LEFT JOIN pengembalian k ON k.peminjaman_detail_id = d.id
     WHERE e.buku_id = ?
     ORDER BY p.tgl_pinjam DESC, d.id DESC",
    [$id]
);
$warna = ['tersedia' => 'success', 'dipinjam' => 'warning', 'direservasi' => 'info', 'rusak' => 'danger', 'hilang' => 'dark'];

$judul = $b['judul'];
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <a class="btn btn-sm btn-link px-0" href="<?= url('modules/master/buku/index.php') ?>"><i class="bi bi-arrow-left"></i> Daftar buku</a>
    <div>
        <?php if (is_file(ROOT_DIR . '/modules/master/eksemplar/label.php')): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= url('modules/master/eksemplar/label.php?buku_id=' . $id) ?>" target="_blank"><i class="bi bi-upc"></i> Cetak label</a>
        <?php endif; ?>
        <a class="btn btn-sm btn-primary" href="<?= url('modules/master/buku/form.php?id=' . $id) ?>"><i class="bi bi-pencil"></i> Ubah</a>
    </div>
</div>
<div class="row g-4">
    <div class="col-md-3"><div style="max-width: 240px"><?= cover_html($b, 'besar') ?></div></div>
    <div class="col-md-9">
        <h1 class="h3 mb-1"><?= e($b['judul']) ?> <?= $b['is_active'] ? '' : '<span class="badge text-bg-secondary fs-6 align-middle">Nonaktif</span>' ?></h1>
        <p class="text-muted mb-3"><?= e($b['penulis']) ?></p>
        <dl class="row mb-3">
            <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9"><?= e($b['kategori']) ?></dd>
            <dt class="col-sm-3">Penerbit</dt><dd class="col-sm-9"><?= e($b['penerbit'] ?? '-') ?><?= $b['kota'] ? ', ' . e($b['kota']) : '' ?></dd>
            <dt class="col-sm-3">Tahun / halaman</dt><dd class="col-sm-9"><?= e((string) ($b['tahun_terbit'] ?? '-')) ?> / <?= e((string) ($b['halaman'] ?? '-')) ?></dd>
            <dt class="col-sm-3">ISBN</dt><dd class="col-sm-9"><?= e($b['isbn'] ?? '-') ?></dd>
            <dt class="col-sm-3">Harga</dt><dd class="col-sm-9"><?= rupiah((int) $b['harga']) ?></dd>
        </dl>
        <?php if ($b['sinopsis']): ?><p><?= nl2br(e($b['sinopsis'])) ?></p><?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Eksemplar (<?= count($eksemplar) ?>)</span>
        <?php if (is_file(ROOT_DIR . '/modules/master/eksemplar/form.php')): ?>
            <a class="btn btn-sm btn-outline-primary" href="<?= url('modules/master/eksemplar/form.php?buku_id=' . $id) ?>"><i class="bi bi-plus"></i> Tambah eksemplar</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Kode</th><th>Rak</th><th>Kondisi</th><th>Status</th><th>Pengadaan</th><th>Sumber</th></tr></thead>
            <tbody>
            <?php foreach ($eksemplar as $x): ?>
                <tr>
                    <td class="font-monospace"><?= e($x['kode_eksemplar']) ?></td>
                    <td><?= e($x['rak'] ?? '-') ?></td>
                    <td><?= e($x['kondisi']) ?></td>
                    <td><span class="badge text-bg-<?= $warna[$x['status']] ?>"><?= e($x['status']) ?></span></td>
                    <td><?= tgl($x['tgl_pengadaan']) ?></td>
                    <td><?= e($x['sumber']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white fw-semibold">Riwayat peminjaman</div>
    <div class="card-body">
        <table class="table table-sm align-middle datatable">
            <thead><tr><th>No. transaksi</th><th>Tgl pinjam</th><th>Anggota</th><th>Eksemplar</th><th>Jatuh tempo</th><th>Kembali</th></tr></thead>
            <tbody>
            <?php foreach ($riwayat as $h): ?>
                <tr>
                    <td><?= e($h['no_transaksi']) ?></td>
                    <td data-order="<?= e($h['tgl_pinjam']) ?>"><?= tgl($h['tgl_pinjam']) ?></td>
                    <td><?= e($h['anggota']) ?> <span class="small text-muted"><?= e($h['kode_anggota']) ?></span></td>
                    <td class="font-monospace"><?= e($h['kode_eksemplar']) ?></td>
                    <td><?= tgl($h['tgl_jatuh_tempo']) ?></td>
                    <td><?= $h['tgl_kembali'] ? tgl($h['tgl_kembali']) . ' (' . e($h['kondisi']) . ')' : '<span class="badge text-bg-warning">Dipinjam</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
