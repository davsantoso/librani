<?php
require __DIR__ . '/../../includes/init.php';
require_role('admin');

if (is_post()) {
    csrf_check();
    $kv = array_filter($_POST['nilai'] ?? [], 'is_string');
    try {
        simpan_setting($kv);
        flash('sukses', 'Pengaturan disimpan. Aturan baru berlaku untuk transaksi berikutnya.');
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
    redirect('modules/pengaturan/index.php');
}

$semua = array_column(db_all('SELECT kunci, nilai, keterangan FROM pengaturan'), null, 'kunci');
$grup = [
    'Identitas perpustakaan (kop laporan)' => ['nama_perpustakaan' => '', 'alamat_perpustakaan' => ''],
    'Peminjaman & denda' => ['maks_pinjam' => 'eksemplar', 'lama_pinjam' => 'hari', 'denda_per_hari' => 'Rp', 'masa_berlaku_anggota' => 'hari'],
    'Perpanjangan & reservasi' => ['maks_perpanjangan' => 'kali', 'lama_perpanjangan' => 'hari', 'maks_reservasi' => 'judul', 'batas_ambil_reservasi' => 'hari'],
    'Denda rusak/hilang (dari harga buku)' => ['denda_rusak_persen' => '%', 'denda_hilang_persen' => '%'],
];

$judul = 'Pengaturan';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-1">Pengaturan Aturan</h1>
<p class="text-muted">Nilai aturan disalin ke setiap transaksi saat dibuat, jadi perubahan di sini tidak mengubah hitungan transaksi lama.</p>
<form method="post" style="max-width: 860px">
    <?= csrf_field() ?>
    <?php foreach ($grup as $nama_grup => $kunci_grup): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><?= e($nama_grup) ?></div>
            <div class="card-body"><div class="row g-3">
                <?php foreach ($kunci_grup as $k => $satuan): ?>
                    <?php if (!isset($semua[$k])) continue; ?>
                    <div class="<?= $satuan === '' ? 'col-md-6' : 'col-md-3' ?>">
                        <label class="form-label small fw-semibold" for="<?= $k ?>"><?= e($semua[$k]['keterangan'] ?? $k) ?></label>
                        <div class="input-group">
                            <?php if ($satuan === 'Rp'): ?><span class="input-group-text">Rp</span><?php endif; ?>
                            <input class="form-control" id="<?= $k ?>" name="nilai[<?= $k ?>]" value="<?= e($semua[$k]['nilai']) ?>"
                                <?= $satuan === '' ? 'maxlength="255"' : 'type="number" min="0" required' ?>>
                            <?php if ($satuan !== '' && $satuan !== 'Rp'): ?><span class="input-group-text"><?= e($satuan) ?></span><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div></div>
        </div>
    <?php endforeach; ?>
    <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan pengaturan</button>
</form>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
