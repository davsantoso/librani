<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
$r = $id ? db_row('SELECT * FROM penerbit WHERE id = ?', [$id]) : ['nama' => '', 'kota' => '', 'telepon' => ''];
if (!$r) {
    flash('gagal', 'Penerbit tidak ditemukan.');
    redirect('modules/master/penerbit/index.php');
}
$errors = [];

if (is_post()) {
    csrf_check();
    $r = ['nama' => post('nama'), 'kota' => post('kota'), 'telepon' => post('telepon')];
    if ($r['nama'] === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if ($r['telepon'] !== '' && !preg_match('/^[0-9+\-() ]{5,20}$/', $r['telepon'])) {
        $errors[] = 'Telepon tidak valid.';
    }
    if (!$errors) {
        if ($id) {
            db()->prepare('UPDATE penerbit SET nama = ?, kota = ?, telepon = ? WHERE id = ?')->execute([$r['nama'], $r['kota'], $r['telepon'], $id]);
        } else {
            db()->prepare('INSERT INTO penerbit (nama, kota, telepon) VALUES (?, ?, ?)')->execute([$r['nama'], $r['kota'], $r['telepon']]);
        }
        flash('sukses', 'Penerbit tersimpan.');
        redirect('modules/master/penerbit/index.php');
    }
}

$judul = ($id ? 'Ubah' : 'Tambah') . ' Penerbit';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<div class="card shadow-sm" style="max-width: 640px"><div class="card-body">
    <?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" value="<?= e($r['nama']) ?>" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label" for="kota">Kota</label><input class="form-control" id="kota" name="kota" value="<?= e($r['kota']) ?>" maxlength="50"></div>
        <div class="mb-3"><label class="form-label" for="telepon">Telepon</label><input class="form-control" id="telepon" name="telepon" value="<?= e($r['telepon']) ?>" maxlength="20"></div>
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-link" href="<?= url('modules/master/penerbit/index.php') ?>">Batal</a>
    </form>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
