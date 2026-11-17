<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

// Tambah: anggota yang datang langsung ke meja layanan → didaftarkan dan langsung diverifikasi.
// Ubah  : data diri anggota (username & password tidak diubah di sini).
$id = (int) ($_GET['id'] ?? 0);
$kolom = ['nama', 'no_identitas', 'jenis_kelamin', 'tgl_lahir', 'alamat', 'no_hp', 'email', 'username'];
$in = array_fill_keys($kolom, '');
if ($id) {
    $lama = db_row('SELECT a.*, u.email, u.username FROM anggota a JOIN users u ON u.id = a.user_id WHERE a.id = ?', [$id]);
    if (!$lama) {
        flash('gagal', 'Anggota tidak ditemukan.');
        redirect('modules/master/anggota/index.php');
    }
    $in = array_intersect_key($lama, $in) + $in;
}
$errors = [];

if (is_post()) {
    csrf_check();
    foreach ($kolom as $k) {
        $in[$k] = post($k);
    }
    $in['password'] = (string) ($_POST['password'] ?? '');
    $errors = validasi_register($in, $id ?: null);

    $foto = null;
    if (!$errors && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        try {
            $foto = simpan_gambar($_FILES['foto'], 'anggota', 1_000_000, ['image/jpeg', 'image/png'], 400)['file'];
        } catch (RuntimeException $e) {
            $errors[] = 'Foto: ' . $e->getMessage();
        }
    }
    if (!$errors) {
        try {
            if ($id) {
                ubah_anggota($id, $in, $foto);
                flash('sukses', 'Data anggota diperbarui.');
            } else {
                $id = simpan_register($in, $foto);
                $kode = verifikasi_anggota($id, user()['id']);
                flash('sukses', "Anggota terdaftar dan aktif dengan kode $kode.");
            }
            redirect('modules/master/anggota/detail.php?id=' . $id);
        } catch (Throwable $e) {
            hapus_gambar('anggota', $foto);
            $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Data gagal disimpan. Coba lagi.';
        }
    }
}

$judul = $id ? 'Ubah Anggota' : 'Tambah Anggota';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<div class="card shadow-sm" style="max-width: 860px"><div class="card-body">
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="nama">Nama lengkap</label><input class="form-control" id="nama" name="nama" value="<?= e($in['nama']) ?>" required maxlength="100"></div>
            <div class="col-md-6"><label class="form-label" for="no_identitas">No. identitas</label><input class="form-control" id="no_identitas" name="no_identitas" value="<?= e($in['no_identitas']) ?>" required maxlength="30"></div>
            <div class="col-md-4">
                <label class="form-label" for="jenis_kelamin">Jenis kelamin</label>
                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">— pilih —</option>
                    <option value="L" <?= $in['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $in['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label" for="tgl_lahir">Tanggal lahir</label><input class="form-control" id="tgl_lahir" name="tgl_lahir" type="date" max="<?= hari_ini() ?>" value="<?= e($in['tgl_lahir']) ?>" required></div>
            <div class="col-md-4"><label class="form-label" for="no_hp">No. HP</label><input class="form-control" id="no_hp" name="no_hp" value="<?= e($in['no_hp']) ?>" required maxlength="20"></div>
            <div class="col-12"><label class="form-label" for="alamat">Alamat</label><textarea class="form-control" id="alamat" name="alamat" rows="2" required><?= e($in['alamat']) ?></textarea></div>
            <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="<?= e($in['email']) ?>" required maxlength="100"></div>
            <?php if ($id): ?>
                <div class="col-md-6"><label class="form-label">Username</label><input class="form-control" value="<?= e($in['username']) ?>" disabled></div>
            <?php else: ?>
                <div class="col-md-3"><label class="form-label" for="username">Username</label><input class="form-control" id="username" name="username" value="<?= e($in['username']) ?>" required maxlength="50" autocomplete="off"></div>
                <div class="col-md-3"><label class="form-label" for="password">Password awal</label><input class="form-control" id="password" name="password" type="password" minlength="8" required autocomplete="new-password"></div>
            <?php endif; ?>
            <div class="col-12"><label class="form-label" for="foto">Foto (opsional, JPG/PNG maks. 1 MB)</label><input class="form-control" id="foto" name="foto" type="file" accept="image/jpeg,image/png"></div>
        </div>
        <div class="mt-4">
            <button class="btn btn-primary">Simpan</button>
            <a class="btn btn-link" href="<?= url($id ? 'modules/master/anggota/detail.php?id=' . $id : 'modules/master/anggota/index.php') ?>">Batal</a>
        </div>
    </form>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
