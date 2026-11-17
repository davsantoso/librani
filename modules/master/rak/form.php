<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
$r = $id ? db_row('SELECT * FROM rak WHERE id = ?', [$id]) : ['kode' => '', 'nama' => '', 'lantai' => 1];
if (!$r) {
    flash('gagal', 'Rak tidak ditemukan.');
    redirect('modules/master/rak/index.php');
}
$errors = [];

if (is_post()) {
    csrf_check();
    $r = ['kode' => strtoupper(post('kode')), 'nama' => post('nama'), 'lantai' => post('lantai')];
    if (!preg_match('/^[A-Z0-9\-]{1,10}$/', $r['kode'])) {
        $errors[] = 'Kode 1–10 huruf/angka.';
    } elseif (db_val('SELECT 1 FROM rak WHERE kode = ? AND id <> ?', [$r['kode'], $id])) {
        $errors[] = 'Kode sudah dipakai rak lain.';
    }
    if ($r['nama'] === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!ctype_digit((string) $r['lantai']) || (int) $r['lantai'] < 1 || (int) $r['lantai'] > 20) {
        $errors[] = 'Lantai harus angka 1–20.';
    }
    if (!$errors) {
        if ($id) {
            db()->prepare('UPDATE rak SET kode = ?, nama = ?, lantai = ? WHERE id = ?')->execute([$r['kode'], $r['nama'], (int) $r['lantai'], $id]);
        } else {
            db()->prepare('INSERT INTO rak (kode, nama, lantai) VALUES (?, ?, ?)')->execute([$r['kode'], $r['nama'], (int) $r['lantai']]);
        }
        flash('sukses', 'Rak tersimpan.');
        redirect('modules/master/rak/index.php');
    }
}

$judul = ($id ? 'Ubah' : 'Tambah') . ' Rak';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<div class="card shadow-sm" style="max-width: 640px"><div class="card-body">
    <?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="kode">Kode rak</label><input class="form-control text-uppercase" id="kode" name="kode" value="<?= e($r['kode']) ?>" required maxlength="10"></div>
        <div class="mb-3"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" value="<?= e($r['nama']) ?>" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label" for="lantai">Lantai</label><input class="form-control" id="lantai" name="lantai" type="number" min="1" max="20" value="<?= e((string) $r['lantai']) ?>" required></div>
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-link" href="<?= url('modules/master/rak/index.php') ?>">Batal</a>
    </form>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
