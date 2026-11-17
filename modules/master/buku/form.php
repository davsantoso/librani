<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$id = (int) ($_GET['id'] ?? 0);
$kosong = ['isbn' => '', 'judul' => '', 'penulis' => '', 'penerbit_id' => '', 'kategori_id' => '', 'tahun_terbit' => '',
    'halaman' => '', 'sinopsis' => '', 'harga' => '0', 'foto_cover' => null, 'foto_thumb' => null,
    'jumlah_eksemplar' => '1', 'rak_id' => '', 'sumber' => 'beli'];
$r = $id ? db_row('SELECT * FROM buku WHERE id = ?', [$id]) : $kosong;
if (!$r) {
    flash('gagal', 'Buku tidak ditemukan.');
    redirect('modules/master/buku/index.php');
}
$r += $kosong;
$error = null;

if (is_post()) {
    csrf_check();
    $in = array_map(fn($v) => is_string($v) ? trim($v) : '', $_POST);
    try {
        $id_baru = simpan_buku($in, $_FILES['cover'] ?? null, $id ?: null);
        flash('sukses', $id ? 'Buku diperbarui.' : 'Buku ditambahkan beserta ' . (int) $in['jumlah_eksemplar'] . ' eksemplar.');
        redirect('modules/master/buku/detail.php?id=' . $id_baru);
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
        $r = $in + $r;
    }
}

$kategori = db_all('SELECT id, nama, is_active FROM kategori WHERE is_active = 1 OR id = ? ORDER BY nama', [(int) $r['kategori_id']]);
$penerbit = db_all('SELECT id, nama, is_active FROM penerbit WHERE is_active = 1 OR id = ? ORDER BY nama', [(int) $r['penerbit_id']]);
$rak = db_all('SELECT id, kode, nama FROM rak WHERE is_active = 1 ORDER BY kode');

$judul = ($id ? 'Ubah' : 'Tambah') . ' Buku';
$js_tambahan = ['assets/js/pratinjau.js'];
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body">
                <div id="pratinjau-cover" class="mb-3" style="max-width: 220px"><?= cover_html($r, 'besar') ?></div>
                <label class="form-label" for="cover">Foto sampul</label>
                <input class="form-control" id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" data-pratinjau="#pratinjau-cover">
                <div class="form-text">JPG, PNG, atau WEBP, maks. 2 MB.</div>
                <?php if ($id && $r['foto_cover']): ?>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="hapus_cover" name="hapus_cover" value="1">
                        <label class="form-check-label" for="hapus_cover">Hapus sampul</label>
                    </div>
                <?php endif; ?>
            </div></div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm"><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label" for="judul">Judul</label><input class="form-control" id="judul" name="judul" value="<?= e($r['judul']) ?>" required maxlength="200"></div>
                    <div class="col-md-4"><label class="form-label" for="isbn">ISBN (opsional)</label><input class="form-control" id="isbn" name="isbn" value="<?= e($r['isbn']) ?>" maxlength="20"></div>
                    <div class="col-md-6"><label class="form-label" for="penulis">Penulis</label><input class="form-control" id="penulis" name="penulis" value="<?= e($r['penulis']) ?>" required maxlength="150"></div>
                    <div class="col-md-6">
                        <label class="form-label" for="penerbit_id">Penerbit</label>
                        <select class="form-select" id="penerbit_id" name="penerbit_id">
                            <option value="">— tidak diketahui —</option>
                            <?php foreach ($penerbit as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (string) $p['id'] === (string) $r['penerbit_id'] ? 'selected' : '' ?>><?= e($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="kategori_id">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                            <option value="">— pilih —</option>
                            <?php foreach ($kategori as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= (string) $k['id'] === (string) $r['kategori_id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><label class="form-label" for="tahun_terbit">Tahun</label><input class="form-control" id="tahun_terbit" name="tahun_terbit" type="number" min="1000" max="<?= date('Y') + 1 ?>" value="<?= e((string) $r['tahun_terbit']) ?>"></div>
                    <div class="col-md-2"><label class="form-label" for="halaman">Halaman</label><input class="form-control" id="halaman" name="halaman" type="number" min="0" value="<?= e((string) $r['halaman']) ?>"></div>
                    <div class="col-md-4"><label class="form-label" for="harga">Harga (Rp)</label><input class="form-control" id="harga" name="harga" type="number" min="0" value="<?= e((string) $r['harga']) ?>"><div class="form-text">Dipakai untuk denda rusak/hilang.</div></div>
                    <div class="col-12"><label class="form-label" for="sinopsis">Sinopsis</label><textarea class="form-control" id="sinopsis" name="sinopsis" rows="4"><?= e($r['sinopsis']) ?></textarea></div>
                    <?php if (!$id): ?>
                        <div class="col-12"><hr class="my-1"><div class="fw-semibold">Eksemplar awal</div></div>
                        <div class="col-md-4"><label class="form-label" for="jumlah_eksemplar">Jumlah eksemplar</label><input class="form-control" id="jumlah_eksemplar" name="jumlah_eksemplar" type="number" min="1" max="50" value="<?= e((string) $r['jumlah_eksemplar']) ?>" required></div>
                        <div class="col-md-4">
                            <label class="form-label" for="rak_id">Rak</label>
                            <select class="form-select" id="rak_id" name="rak_id">
                                <option value="">— belum ditentukan —</option>
                                <?php foreach ($rak as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= (string) $k['id'] === (string) $r['rak_id'] ? 'selected' : '' ?>><?= e($k['kode'] . ' — ' . $k['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="sumber">Sumber</label>
                            <select class="form-select" id="sumber" name="sumber">
                                <option value="beli">Beli</option>
                                <option value="hibah" <?= $r['sumber'] === 'hibah' ? 'selected' : '' ?>>Hibah</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary">Simpan</button>
                    <a class="btn btn-link" href="<?= url($id ? 'modules/master/buku/detail.php?id=' . $id : 'modules/master/buku/index.php') ?>">Batal</a>
                </div>
            </div></div>
        </div>
    </div>
</form>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
