<?php
// Aturan bisnis file master: register/verifikasi anggota, buku, eksemplar, soft delete.

// ===================== Register anggota =====================

/** @return string[] daftar pesan error (kosong = valid) */
function validasi_register(array $in): array
{
    $v = fn(string $k) => trim((string) ($in[$k] ?? ''));
    $err = [];
    $wajib = [
        'nama' => 'Nama', 'no_identitas' => 'No. identitas', 'jenis_kelamin' => 'Jenis kelamin',
        'tgl_lahir' => 'Tanggal lahir', 'alamat' => 'Alamat', 'no_hp' => 'No. HP',
        'email' => 'Email', 'username' => 'Username', 'password' => 'Password',
    ];
    foreach ($wajib as $k => $label) {
        if ($v($k) === '') {
            $err[] = "$label wajib diisi.";
        }
    }
    if ($v('jenis_kelamin') !== '' && !in_array($v('jenis_kelamin'), ['L', 'P'], true)) {
        $err[] = 'Jenis kelamin tidak valid.';
    }
    if ($v('tgl_lahir') !== '') {
        $d = DateTime::createFromFormat('!Y-m-d', $v('tgl_lahir'));
        if (!$d || $d->format('Y-m-d') !== $v('tgl_lahir') || $v('tgl_lahir') >= hari_ini()) {
            $err[] = 'Tanggal lahir tidak valid.';
        }
    }
    if ($v('email') !== '' && !filter_var($v('email'), FILTER_VALIDATE_EMAIL)) {
        $err[] = 'Format email tidak valid.';
    }
    if ($v('username') !== '' && !preg_match('/^[A-Za-z0-9_.]{3,50}$/', $v('username'))) {
        $err[] = 'Username 3–50 karakter: huruf, angka, titik, atau garis bawah.';
    }
    if ($v('no_hp') !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $v('no_hp'))) {
        $err[] = 'No. HP tidak valid.';
    }
    if (($in['password'] ?? '') !== '' && strlen((string) $in['password']) < 8) {
        $err[] = 'Password minimal 8 karakter.';
    }
    if ($v('username') !== '' && db_val('SELECT 1 FROM users WHERE username = ?', [$v('username')])) {
        $err[] = 'Username sudah dipakai.';
    }
    if ($v('email') !== '' && db_val('SELECT 1 FROM users WHERE email = ?', [$v('email')])) {
        $err[] = 'Email sudah dipakai.';
    }
    if ($v('no_identitas') !== '' && db_val('SELECT 1 FROM anggota WHERE no_identitas = ?', [$v('no_identitas')])) {
        $err[] = 'No. identitas sudah terdaftar.';
    }
    return $err;
}

/** Simpan pendaftar baru (status menunggu). Panggil validasi_register() lebih dulu. */
function simpan_register(array $in, ?string $foto): int
{
    $v = fn(string $k) => trim((string) ($in[$k] ?? ''));
    $db = db();
    $db->beginTransaction();
    try {
        $db->prepare("INSERT INTO users (nama, username, email, password_hash, role) VALUES (?, ?, ?, ?, 'anggota')")
            ->execute([$v('nama'), $v('username'), $v('email'), password_hash((string) $in['password'], PASSWORD_DEFAULT)]);
        $user_id = (int) $db->lastInsertId();
        $db->prepare(
            'INSERT INTO anggota (user_id, no_identitas, nama, jenis_kelamin, tgl_lahir, alamat, no_hp, foto, status, tgl_daftar)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $user_id, $v('no_identitas'), $v('nama'), $v('jenis_kelamin'), $v('tgl_lahir'),
            $v('alamat'), $v('no_hp'), $foto, 'menunggu', hari_ini(),
        ]);
        $id = (int) $db->lastInsertId();
        $db->commit();
        return $id;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function ganti_password(int $user_id, string $lama, string $baru): void
{
    $hash = (string) db_val('SELECT password_hash FROM users WHERE id = ?', [$user_id]);
    if (!password_verify($lama, $hash)) {
        throw new RuntimeException('Password lama salah.');
    }
    if (strlen($baru) < 8) {
        throw new RuntimeException('Password baru minimal 8 karakter.');
    }
    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($baru, PASSWORD_DEFAULT), $user_id]);
}

// ===================== Soft delete umum =====================

/**
 * Hapus permanen jika belum dipakai tabel lain; jika sudah dipakai, nonaktifkan (is_active = 0).
 * $relasi = [[tabel_anak, kolom_fk], ...]. Nama tabel/kolom divalidasi whitelist karena tidak bisa di-bind.
 * @return string 'dihapus' | 'dinonaktifkan'
 */
function hapus_atau_nonaktifkan(string $tabel, int $id, array $relasi): string
{
    $boleh = ['kategori', 'penerbit', 'rak', 'buku', 'users'];
    $anak_boleh = ['buku', 'eksemplar', 'peminjaman', 'pengembalian', 'denda', 'anggota', 'perpanjangan', 'reservasi'];
    if (!in_array($tabel, $boleh, true)) {
        throw new InvalidArgumentException("Tabel $tabel tidak bisa dihapus lewat fungsi ini.");
    }
    foreach ($relasi as [$anak, $kolom]) {
        if (!in_array($anak, $anak_boleh, true) || !preg_match('/^[a-z_]+$/', $kolom)) {
            throw new InvalidArgumentException('Relasi tidak valid.');
        }
    }
    foreach ($relasi as [$anak, $kolom]) {
        if (db_val("SELECT 1 FROM $anak WHERE $kolom = ? LIMIT 1", [$id])) {
            db()->prepare("UPDATE $tabel SET is_active = 0 WHERE id = ?")->execute([$id]);
            return 'dinonaktifkan';
        }
    }
    db()->prepare("DELETE FROM $tabel WHERE id = ?")->execute([$id]);
    return 'dihapus';
}
