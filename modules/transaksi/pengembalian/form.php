<?php
require __DIR__ . '/../../../includes/init.php';
require_role('petugas');

if (is_post()) {
    csrf_check();
    $items = [];
    foreach ((array) ($_POST['kembali'] ?? []) as $detail_id => $v) {
        $items[] = ['detail_id' => (int) $detail_id, 'kondisi' => (string) ($_POST['kondisi'][$detail_id] ?? 'baik')];
    }
    try {
        $no = proses_kembali($items, user()['id']);
        flash('sukses', "Pengembalian $no tersimpan.");
        redirect('modules/transaksi/pengembalian/bukti.php?no=' . urlencode($no));
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
        redirect('modules/transaksi/pengembalian/form.php?q=' . urlencode(post('q')));
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$items = cari_pinjaman_aktif($q);
$pratinjau = array_column(pratinjau_kembali(array_column($items, 'detail_id')), null, 'detail_id');

$judul = 'Pengembalian';
$js_tambahan = ['assets/js/pengembalian.js'];
if (is_file(ROOT_DIR . '/assets/js/scan.js')) {
    array_unshift($js_tambahan, 'assets/vendor/html5-qrcode.min.js', 'assets/js/scan.js');
}
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Proses Pengembalian</h1>
    <a class="btn btn-sm btn-link" href="<?= url('modules/transaksi/pengembalian/index.php') ?>"><i class="bi bi-list"></i> Riwayat pengembalian</a>
</div>
<form class="card shadow-sm mb-4" method="get">
    <div class="card-body">
        <label class="form-label" for="q">No. transaksi, kode anggota, atau scan kode eksemplar</label>
        <div class="input-group">
            <input class="form-control font-monospace" id="q" name="q" value="<?= e($q) ?>" placeholder="PJM-20261117-0001 / AGT-2026-0001 / BK0001-01" autofocus autocomplete="off">
            <?php if (is_file(ROOT_DIR . '/assets/js/scan.js')): ?>
                <button class="btn btn-outline-secondary" type="button" data-scan="#q" data-scan-submit="1" title="Scan kamera"><i class="bi bi-upc-scan"></i></button>
            <?php endif; ?>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
        </div>
    </div>
</form>

<?php if ($q !== '' && !$items): ?>
    <div class="alert alert-warning">Tidak ada pinjaman aktif untuk “<?= e($q) ?>”.</div>
<?php elseif ($items): ?>
<form method="post" id="form-kembali">
    <?= csrf_field() ?>
    <input type="hidden" name="q" value="<?= e($q) ?>">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong><?= e($items[0]['anggota']) ?></strong> <span class="font-monospace small"><?= e($items[0]['kode_anggota']) ?></span>
            <span class="text-muted small ms-2">Tanggal kembali: <?= tgl(hari_ini()) ?></span>
        </div>
        <div class="card-body d-grid gap-2">
            <?php foreach ($items as $i): ?>
                <?php $pr = $pratinjau[$i['detail_id']]; ?>
                <div class="border rounded p-2 d-flex flex-wrap gap-3 align-items-center baris-kembali"
                     data-terlambat="<?= $pr['denda_terlambat'] ?>" data-rusak="<?= $pr['denda_rusak'] ?>" data-hilang="<?= $pr['denda_hilang'] ?>">
                    <input class="form-check-input fs-4 m-0" type="checkbox" name="kembali[<?= $i['detail_id'] ?>]" value="1" <?= $i['dipilih'] ? 'checked' : '' ?> aria-label="Kembalikan <?= e($i['kode_eksemplar']) ?>">
                    <div class="cover-kecil"><?= cover_html($i) ?></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold"><?= e($i['judul']) ?></div>
                        <div class="small"><span class="font-monospace"><?= e($i['kode_eksemplar']) ?></span> · <?= e($i['no_transaksi']) ?></div>
                        <div class="small">Jatuh tempo <?= tgl($i['tgl_jatuh_tempo']) ?>
                            <?php if ($pr['hari'] > 0): ?>
                                · <span class="text-danger fw-semibold">terlambat <?= $pr['hari'] ?> hari = <?= rupiah($pr['denda_terlambat']) ?></span>
                            <?php else: ?>
                                · <span class="text-success">tepat waktu</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="width: 160px">
                        <select class="form-select form-select-sm" name="kondisi[<?= $i['detail_id'] ?>]" aria-label="Kondisi">
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak<?= $pr['denda_rusak'] ? ' (+' . rupiah($pr['denda_rusak']) . ')' : '' ?></option>
                            <option value="hilang">Hilang<?= $pr['denda_hilang'] ? ' (+' . rupiah($pr['denda_hilang']) . ')' : '' ?></option>
                        </select>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div>Total denda: <strong class="fs-5" id="total-denda">Rp0</strong></div>
            <button class="btn btn-primary" id="btn-simpan-kembali"><i class="bi bi-check2-circle"></i> Simpan pengembalian</button>
        </div>
    </div>
</form>
<?php endif; ?>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
