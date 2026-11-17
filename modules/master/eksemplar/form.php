<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

// ?id=  → ubah rak/kondisi/sumber/tanggal satu eksemplar (status tidak bisa diubah di sini)
// ?buku_id= atau kosong → tambah eksemplar baru untuk buku yang sudah ada
$id = (int) ($_GET['id'] ?? 0);
$x = $id ? db_row('SELECT e.*, b.judul FROM eksemplar e JOIN buku b ON b.id = e.buku_id WHERE e.id = ?', [$id]) : null;
if ($id && !$x) {
    flash('gagal', 'Eksemplar tidak ditemukan.');
    redirect('modules/master/eksemplar/index.php');
}
$in = $x ?? ['buku_id' => (int) ($_GET['buku_id'] ?? 0), 'rak_id' => '', 'kondisi' => 'baik', 'sumber' => 'beli', 'tgl_pengadaan' => hari_ini(), 'jumlah' => 1];
$error = null;

if (is_post()) {
    csrf_check();
    $in = array_merge($in, [
        'buku_id' => $id ? $x['buku_id'] : (int) post('buku_id'),
        'rak_id' => post('rak_id'),
        'kondisi' => post('kondisi'),
        'sumber' => post('sumber'),
        'tgl_pengadaan' => post('tgl_pengadaan'),
        'jumlah' => (int) post('jumlah', '1'),
    ]);
    $rak_id = $in['rak_id'] === '' ? null : (int) $in['rak_id'];
    if (!in_array($in['kondisi'], ['baik', 'cukup', 'buruk'], true) || !in_array($in['sumber'], ['beli', 'hibah'], true)) {
        $error = 'Kondisi atau sumber tidak valid.';
    } elseif (!DateTime::createFromFormat('!Y-m-d', $in['tgl_pengadaan']) || $in['tgl_pengadaan'] > hari_ini()) {
        $error = 'Tanggal pengadaan tidak valid.';
    } elseif ($rak_id && !db_val('SELECT 1 FROM rak WHERE id = ?', [$rak_id])) {
        $error = 'Rak tidak valid.';
    } elseif ($id) {
        db()->prepare('UPDATE eksemplar SET rak_id = ?, kondisi = ?, sumber = ?, tgl_pengadaan = ? WHERE id = ?')
            ->execute([$rak_id, $in['kondisi'], $in['sumber'], $in['tgl_pengadaan'], $id]);
        flash('sukses', 'Eksemplar ' . $x['kode_eksemplar'] . ' diperbarui.');
        redirect('modules/master/eksemplar/index.php?buku_id=' . $x['buku_id']);
    } elseif (!db_val('SELECT 1 FROM buku WHERE id = ?', [$in['buku_id']])) {
        $error = 'Buku wajib dipilih.';
    } elseif ($in['jumlah'] < 1 || $in['jumlah'] > 50) {
        $error = 'Jumlah eksemplar 1–50.';
    } else {
        db()->beginTransaction();
        $ids = buat_eksemplar($in['buku_id'], $in['jumlah'], $rak_id, $in['sumber'], $in['tgl_pengadaan']);
        if ($in['kondisi'] !== 'baik') {
            db()->prepare('UPDATE eksemplar SET kondisi = ? WHERE id IN (' . implode(',', $ids) . ')')->execute([$in['kondisi']]);
        }
        db()->commit();
        flash('sukses', count($ids) . ' eksemplar ditambahkan.');
        redirect('modules/master/buku/detail.php?id=' . $in['buku_id']);
    }
}

$buku = db_all('SELECT id, judul FROM buku WHERE is_active = 1 OR id = ? ORDER BY judul', [(int) $in['buku_id']]);
$rak = db_all('SELECT id, kode, nama FROM rak WHERE is_active = 1 OR id = ? ORDER BY kode', [(int) $in['rak_id']]);

$judul = $id ? 'Ubah Eksemplar ' . $x['kode_eksemplar'] : 'Tambah Eksemplar';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<div class="card shadow-sm" style="max-width: 720px"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="row g-3">
            <?php if ($id): ?>
                <div class="col-12"><label class="form-label">Buku</label><input class="form-control" value="<?= e($x['judul']) ?>" disabled></div>
            <?php else: ?>
                <div class="col-md-9">
                    <label class="form-label" for="buku_id">Buku</label>
                    <select class="form-select" id="buku_id" name="buku_id" required>
                        <option value="">— pilih buku —</option>
                        <?php foreach ($buku as $b): ?><option value="<?= $b['id'] ?>" <?= (int) $in['buku_id'] === $b['id'] ? 'selected' : '' ?>><?= e($b['judul']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label" for="jumlah">Jumlah</label><input class="form-control" id="jumlah" name="jumlah" type="number" min="1" max="50" value="<?= (int) $in['jumlah'] ?>" required></div>
            <?php endif; ?>
            <div class="col-md-6">
                <label class="form-label" for="rak_id">Rak</label>
                <select class="form-select" id="rak_id" name="rak_id">
                    <option value="">— belum ditentukan —</option>
                    <?php foreach ($rak as $r): ?><option value="<?= $r['id'] ?>" <?= (string) $in['rak_id'] === (string) $r['id'] ? 'selected' : '' ?>><?= e($r['kode'] . ' — ' . $r['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="kondisi">Kondisi fisik</label>
                <select class="form-select" id="kondisi" name="kondisi">
                    <?php foreach (['baik', 'cukup', 'buruk'] as $k): ?><option value="<?= $k ?>" <?= $in['kondisi'] === $k ? 'selected' : '' ?>><?= ucfirst($k) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="sumber">Sumber</label>
                <select class="form-select" id="sumber" name="sumber">
                    <option value="beli">Beli</option>
                    <option value="hibah" <?= $in['sumber'] === 'hibah' ? 'selected' : '' ?>>Hibah</option>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label" for="tgl_pengadaan">Tanggal pengadaan</label><input class="form-control" id="tgl_pengadaan" name="tgl_pengadaan" type="date" max="<?= hari_ini() ?>" value="<?= e((string) $in['tgl_pengadaan']) ?>" required></div>
        </div>
        <?php if ($id): ?><p class="form-text mt-3">Status (<?= e($x['status']) ?>) hanya berubah lewat transaksi atau tombol "Tandai" di daftar eksemplar.</p><?php endif; ?>
        <div class="mt-3">
            <button class="btn btn-primary">Simpan</button>
            <a class="btn btn-link" href="<?= url('modules/master/eksemplar/index.php') ?>">Batal</a>
        </div>
    </form>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
