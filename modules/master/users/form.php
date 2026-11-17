<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
$r = $id
    ? db_row("SELECT * FROM users WHERE id = ? AND role IN ('admin','petugas')", [$id])
    : ['nama' => '', 'username' => '', 'email' => '', 'role' => 'petugas', 'is_active' => 1];
if (!$r) {
    flash('gagal', 'User tidak ditemukan.');
    redirect('modules/master/users/index.php');
}
$diri_sendiri = $id === user()['id'];
$error = null;

if (is_post()) {
    csrf_check();
    $r = [
        'nama' => post('nama'), 'username' => post('username'), 'email' => post('email'),
        'role' => $diri_sendiri ? 'admin' : post('role'),          // admin tidak bisa menurunkan role sendiri
        'is_active' => $diri_sendiri ? 1 : (int) isset($_POST['is_active']),
        'password' => (string) ($_POST['password'] ?? ''),
    ];
    try {
        simpan_user($r, $id ?: null);
        flash('sukses', 'User tersimpan.');
        redirect('modules/master/users/index.php');
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

$judul = ($id ? 'Ubah' : 'Tambah') . ' User Petugas';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<div class="card shadow-sm" style="max-width: 640px"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" value="<?= e($r['nama']) ?>" required maxlength="100"></div>
        <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label" for="username">Username</label><input class="form-control" id="username" name="username" value="<?= e($r['username']) ?>" required maxlength="50"></div>
            <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="<?= e($r['email']) ?>" required maxlength="100"></div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label" for="role">Role</label>
                <select class="form-select" id="role" name="role" <?= $diri_sendiri ? 'disabled' : '' ?>>
                    <option value="petugas" <?= $r['role'] === 'petugas' ? 'selected' : '' ?>>Petugas</option>
                    <option value="admin" <?= $r['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Password <?= $id ? '(kosongkan jika tidak diganti)' : '' ?></label>
                <input class="form-control" id="password" name="password" type="password" minlength="8" <?= $id ? '' : 'required' ?> autocomplete="new-password">
            </div>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $r['is_active'] ? 'checked' : '' ?> <?= $diri_sendiri ? 'disabled' : '' ?>>
            <label class="form-check-label" for="is_active">Akun aktif (bisa login)</label>
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-link" href="<?= url('modules/master/users/index.php') ?>">Batal</a>
    </form>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
