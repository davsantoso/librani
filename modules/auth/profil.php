<?php
require __DIR__ . '/../../includes/init.php';
require_login();

$u = user();
$anggota = $u['role'] === 'anggota' ? anggota_saya() : null;

if (is_post()) {
    csrf_check();
    try {
        if (($_POST['aksi'] ?? '') === 'password') {
            if (($_POST['baru'] ?? '') !== ($_POST['baru2'] ?? '')) {
                throw new RuntimeException('Konfirmasi password baru tidak sama.');
            }
            ganti_password($u['id'], (string) ($_POST['lama'] ?? ''), (string) ($_POST['baru'] ?? ''));
            flash('sukses', 'Password berhasil diganti.');
        } elseif ($anggota) {
            // Anggota: nama & no. identitas terkunci setelah verifikasi; yang bisa diubah alamat, HP, foto.
            $alamat = post('alamat');
            $no_hp = post('no_hp');
            if ($alamat === '' || !preg_match('/^[0-9+\- ]{8,20}$/', $no_hp)) {
                throw new RuntimeException('Alamat wajib diisi dan No. HP harus valid.');
            }
            $foto = $anggota['foto'];
            if (($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $foto = simpan_gambar($_FILES['foto'], 'anggota', 1_000_000, ['image/jpeg', 'image/png'], 400)['file'];
            }
            db()->prepare('UPDATE anggota SET alamat = ?, no_hp = ?, foto = ? WHERE id = ?')->execute([$alamat, $no_hp, $foto, $anggota['id']]);
            if ($foto !== $anggota['foto']) {
                hapus_gambar('anggota', $anggota['foto']);
            }
            flash('sukses', 'Profil berhasil diperbarui.');
        } else {
            $nama = post('nama');
            $email = post('email');
            if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Nama wajib diisi dan email harus valid.');
            }
            if (db_val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [$email, $u['id']])) {
                throw new RuntimeException('Email sudah dipakai.');
            }
            db()->prepare('UPDATE users SET nama = ?, email = ? WHERE id = ?')->execute([$nama, $email, $u['id']]);
            flash('sukses', 'Profil berhasil diperbarui.');
        }
    } catch (RuntimeException $e) {
        flash('gagal', $e->getMessage());
    }
    redirect('modules/auth/profil.php');
}

$judul = 'Profil';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3">Profil &amp; Password</h1>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Data diri</div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="profil">
                    <div class="mb-3"><label class="form-label">Username</label><input class="form-control" value="<?= e($u['username']) ?>" disabled></div>
                    <?php if ($anggota): ?>
                        <div class="d-flex gap-3 mb-3 align-items-center">
                            <?php if ($anggota['foto']): ?>
                                <img src="<?= url('uploads/anggota/' . $anggota['foto']) ?>" alt="Foto" class="rounded" style="width:80px;height:100px;object-fit:cover">
                            <?php endif; ?>
                            <div>
                                <div class="fw-semibold"><?= e($anggota['nama']) ?></div>
                                <div class="small text-muted">Kode: <?= e($anggota['kode_anggota'] ?? 'belum diverifikasi') ?> · Status: <?= e($anggota['status']) ?></div>
                                <div class="small text-muted">Berlaku s.d. <?= tgl($anggota['tgl_berlaku']) ?></div>
                            </div>
                        </div>
                        <div class="mb-3"><label class="form-label" for="alamat">Alamat</label><textarea class="form-control" id="alamat" name="alamat" rows="2" required><?= e($anggota['alamat']) ?></textarea></div>
                        <div class="mb-3"><label class="form-label" for="no_hp">No. HP</label><input class="form-control" id="no_hp" name="no_hp" value="<?= e($anggota['no_hp']) ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="foto">Ganti foto (JPG/PNG maks. 1 MB)</label><input class="form-control" id="foto" name="foto" type="file" accept="image/jpeg,image/png"></div>
                    <?php else: ?>
                        <div class="mb-3"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" value="<?= e($u['nama']) ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="<?= e($u['email']) ?>" required></div>
                    <?php endif; ?>
                    <button class="btn btn-primary">Simpan profil</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Ganti password</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="password">
                    <div class="mb-3"><label class="form-label" for="lama">Password lama</label><input class="form-control" id="lama" name="lama" type="password" required autocomplete="current-password"></div>
                    <div class="mb-3"><label class="form-label" for="baru">Password baru (min. 8 karakter)</label><input class="form-control" id="baru" name="baru" type="password" minlength="8" required autocomplete="new-password"></div>
                    <div class="mb-3"><label class="form-label" for="baru2">Ulangi password baru</label><input class="form-control" id="baru2" name="baru2" type="password" minlength="8" required autocomplete="new-password"></div>
                    <button class="btn btn-outline-primary">Ganti password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
