<?php
require __DIR__ . '/../../../includes/init.php';
require_role('petugas');

$error = null;
$awal = ['anggota' => (string) ($_GET['anggota'] ?? ''), 'eksemplar' => []];

if (is_post()) {
    csrf_check();
    $anggota_id = (int) ($_POST['anggota_id'] ?? 0);
    $ids = array_map('intval', (array) ($_POST['eksemplar_ids'] ?? []));
    try {
        $pid = simpan_peminjaman($anggota_id, $ids, user()['id']);
        flash('sukses', 'Peminjaman ' . db_val('SELECT no_transaksi FROM peminjaman WHERE id = ?', [$pid]) . ' tersimpan.');
        redirect('modules/transaksi/peminjaman/detail.php?id=' . $pid);
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
        // Pulihkan pilihan agar petugas tidak mengulang dari awal.
        $awal['anggota'] = (string) db_val('SELECT kode_anggota FROM anggota WHERE id = ?', [$anggota_id]);
        if ($ids) {
            $awal['eksemplar'] = array_column(
                db_all('SELECT kode_eksemplar FROM eksemplar WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids),
                'kode_eksemplar'
            );
        }
    }
}

$judul = 'Peminjaman Baru';
$js_tambahan = ['assets/js/peminjaman.js'];
if (is_file(ROOT_DIR . '/assets/js/scan.js')) {
    array_unshift($js_tambahan, 'assets/vendor/html5-qrcode.min.js', 'assets/js/scan.js');
}
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Peminjaman Baru</h1>
    <a class="btn btn-sm btn-link" href="<?= url('modules/transaksi/peminjaman/index.php') ?>"><i class="bi bi-list"></i> Daftar peminjaman</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= e($error) ?></div><?php endif; ?>

<form method="post" id="form-pinjam"
      data-url-anggota="<?= url('modules/transaksi/peminjaman/cari_anggota.php') ?>"
      data-url-eksemplar="<?= url('modules/transaksi/peminjaman/cari_eksemplar.php') ?>"
      data-awal="<?= e(json_encode($awal)) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="anggota_id" id="anggota_id">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">1. Anggota</div>
                <div class="card-body">
                    <label class="form-label" for="cari-anggota">Kode anggota atau nama (bisa scan kartu)</label>
                    <div class="input-group">
                        <input class="form-control" id="cari-anggota" autocomplete="off" placeholder="AGT-2026-0001" autofocus>
                        <button class="btn btn-outline-primary" type="button" id="btn-cari-anggota"><i class="bi bi-search"></i></button>
                        <?php if (is_file(ROOT_DIR . '/assets/js/scan.js')): ?>
                            <button class="btn btn-outline-secondary" type="button" data-scan="#cari-anggota" title="Scan kamera"><i class="bi bi-qr-code-scan"></i></button>
                        <?php endif; ?>
                    </div>
                    <div id="hasil-anggota" class="list-group mt-2"></div>
                    <div id="kartu-anggota" class="mt-3"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    <span>2. Eksemplar</span><span class="small text-muted" id="info-kuota"></span>
                </div>
                <div class="card-body">
                    <label class="form-label" for="kode-eksemplar">Kode eksemplar (ketik lalu Enter, atau scan barcode)</label>
                    <div class="input-group mb-3">
                        <input class="form-control font-monospace" id="kode-eksemplar" autocomplete="off" placeholder="BK0001-01" disabled>
                        <button class="btn btn-outline-primary" type="button" id="btn-tambah-eksemplar" disabled><i class="bi bi-plus-lg"></i> Tambah</button>
                        <?php if (is_file(ROOT_DIR . '/assets/js/scan.js')): ?>
                            <button class="btn btn-outline-secondary" type="button" data-scan="#kode-eksemplar" title="Scan kamera"><i class="bi bi-upc-scan"></i></button>
                        <?php endif; ?>
                    </div>
                    <div id="daftar-eksemplar" class="d-grid gap-2"></div>
                    <p class="text-muted small mb-0 mt-2" id="kosong-eksemplar">Belum ada eksemplar dipilih.</p>
                </div>
                <div class="card-footer bg-white text-end">
                    <span class="me-3 small text-muted">Jatuh tempo: <strong><?= tgl(tambah_hari(hari_ini(), (int) setting('lama_pinjam'))) ?></strong></span>
                    <button class="btn btn-primary" id="btn-simpan" disabled><i class="bi bi-check2-circle"></i> Simpan peminjaman</button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
