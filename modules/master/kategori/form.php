<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
$r = $id ? db_row('SELECT * FROM kategori WHERE id = ?', [$id]) : ['kode' => '', 'nama' => '', 'deskripsi' => ''];
if (!$r) {
    flash('gagal', 'Kategori tidak ditemukan.');
    redirect('modules/master/kategori/index.php');
}
$errors = [];

if (is_post()) {
    csrf_check();
    $r = ['kode' => strtoupper(post('kode')), 'nama' => post('nama'), 'deskripsi' => post('deskripsi')];
    if (!preg_match('/^[A-Z0-9]{2,10}$/', $r['kode'])) {
        $errors[] = 'Kode 2–10 huruf/angka.';
    } elseif (db_val('SELECT 1 FROM kategori WHERE kode = ? AND id <> ?', [$r['kode'], $id])) {
        $errors[] = 'Kode sudah dipakai kategori lain.';
    }
    if ($r['nama'] === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!$errors) {
        if ($id) {
            db()->prepare('UPDATE kategori SET kode = ?, nama = ?, deskripsi = ? WHERE id = ?')->execute([$r['kode'], $r['nama'], $r['deskripsi'], $id]);
        } else {
            db()->prepare('INSERT INTO kategori (kode, nama, deskripsi) VALUES (?, ?, ?)')->execute([$r['kode'], $r['nama'], $r['deskripsi']]);
        }
        flash('sukses', 'Kategori tersimpan.');
        redirect('modules/master/kategori/index.php');
    }
}

$judul = ($id ? 'Ubah' : 'Tambah') . ' Kategori';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<div class="card shadow-sm" style="max-width: 640px"><div class="card-body">
    <?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="kode">Kode</label><input class="form-control text-uppercase" id="kode" name="kode" value="<?= e($r['kode']) ?>" required maxlength="10"></div>
        <div class="mb-3"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" value="<?= e($r['nama']) ?>" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label" for="deskripsi">Deskripsi</label><textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= e($r['deskripsi']) ?></textarea></div>
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-link" href="<?= url('modules/master/kategori/index.php') ?>">Batal</a>
    </form>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
