<?php
require __DIR__ . '/../../includes/init.php';

if (user()) {
    redirect(dashboard_url(user()['role']));
}

$kolom = ['nama', 'no_identitas', 'jenis_kelamin', 'tgl_lahir', 'alamat', 'no_hp', 'email', 'username'];
$in = array_fill_keys($kolom, '');
$errors = [];

if (is_post()) {
    csrf_check();
    foreach ($kolom as $k) {
        $in[$k] = post($k);
    }
    $in['password'] = (string) ($_POST['password'] ?? '');
    $errors = validasi_register($in);
    if ($in['password'] !== ($_POST['password2'] ?? '')) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

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
            simpan_register($in, $foto);
            flash('sukses', 'Pendaftaran berhasil. Silakan masuk; akun akan aktif setelah diverifikasi petugas.');
            redirect('modules/auth/login.php');
        } catch (PDOException $e) {
            hapus_gambar('anggota', $foto);
            $errors[] = 'Data gagal disimpan, kemungkinan username/email/no. identitas baru saja dipakai. Coba lagi.';
        }
    }
}

$judul = 'Daftar Anggota';
$tanpa_sidebar = true;
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="mx-auto my-4 px-3" style="max-width: 752px">
    <div class="text-center mb-3">
        <a href="<?= url() ?>" class="text-decoration-none text-dark"><i class="bi bi-book-half text-primary display-6"></i></a>
        <h1 class="h4 mt-2">Daftar Anggota <?= e(setting('nama_perpustakaan')) ?></h1>
        <p class="text-muted small mb-0">Setelah mendaftar, bawa kartu identitas ke petugas untuk verifikasi.</p>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama">Nama lengkap</label>
                        <input class="form-control" id="nama" name="nama" value="<?= e($in['nama']) ?>" required maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="no_identitas">No. identitas (KTP/kartu pelajar)</label>
                        <input class="form-control" id="no_identitas" name="no_identitas" value="<?= e($in['no_identitas']) ?>" required maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="jenis_kelamin">Jenis kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">— pilih —</option>
                            <option value="L" <?= $in['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $in['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="tgl_lahir">Tanggal lahir</label>
                        <input class="form-control" id="tgl_lahir" name="tgl_lahir" type="date" value="<?= e($in['tgl_lahir']) ?>" required max="<?= hari_ini() ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="alamat">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2" required><?= e($in['alamat']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="no_hp">No. HP</label>
                        <input class="form-control" id="no_hp" name="no_hp" value="<?= e($in['no_hp']) ?>" required maxlength="20" inputmode="tel">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" value="<?= e($in['email']) ?>" required maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-control" id="username" name="username" value="<?= e($in['username']) ?>" required maxlength="50" autocomplete="username">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password2">Ulangi password</label>
                        <input class="form-control" id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="foto">Foto (opsional, JPG/PNG maks. 1 MB)</label>
                        <input class="form-control" id="foto" name="foto" type="file" accept="image/jpeg,image/png">
                    </div>
                </div>
                <button class="btn btn-primary w-100 mt-4">Daftar</button>
            </form>
        </div>
    </div>
    <p class="text-center mt-3 small">Sudah punya akun? <a href="<?= url('modules/auth/login.php') ?>">Masuk</a></p>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
