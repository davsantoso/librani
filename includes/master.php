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

// ===================== User petugas =====================

/** Tambah ($id null) atau ubah user admin/petugas. Password opsional saat ubah. Melempar RuntimeException. */
function simpan_user(array $in, ?int $id = null): int
{
    $v = fn(string $k) => trim((string) ($in[$k] ?? ''));
    if ($id && !db_val("SELECT 1 FROM users WHERE id = ? AND role IN ('admin','petugas')", [$id])) {
        throw new RuntimeException('User tidak ditemukan.');
    }
    if (!in_array($v('role'), ['admin', 'petugas'], true)) {
        throw new RuntimeException('Role tidak valid.');
    }
    if ($v('nama') === '') {
        throw new RuntimeException('Nama wajib diisi.');
    }
    if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $v('username'))) {
        throw new RuntimeException('Username 3–50 karakter: huruf, angka, titik, atau garis bawah.');
    }
    if (!filter_var($v('email'), FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Format email tidak valid.');
    }
    if (db_val('SELECT 1 FROM users WHERE username = ? AND id <> ?', [$v('username'), (int) $id])) {
        throw new RuntimeException('Username sudah dipakai.');
    }
    if (db_val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [$v('email'), (int) $id])) {
        throw new RuntimeException('Email sudah dipakai.');
    }
    $password = (string) ($in['password'] ?? '');
    if ((!$id || $password !== '') && strlen($password) < 8) {
        throw new RuntimeException('Password minimal 8 karakter.');
    }
    $aktif = isset($in['is_active']) ? (int) (bool) $in['is_active'] : 1;

    if ($id) {
        db()->prepare('UPDATE users SET nama = ?, username = ?, email = ?, role = ?, is_active = ? WHERE id = ?')
            ->execute([$v('nama'), $v('username'), $v('email'), $v('role'), $aktif, $id]);
        if ($password !== '') {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        }
        return $id;
    }
    db()->prepare('INSERT INTO users (nama, username, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$v('nama'), $v('username'), $v('email'), password_hash($password, PASSWORD_DEFAULT), $v('role'), $aktif]);
    return (int) db()->lastInsertId();
}

// ===================== Buku & eksemplar =====================

/**
 * Buat $jumlah eksemplar baru (status tersedia) untuk buku; kode melanjutkan urutan terakhir.
 * @return int[] id eksemplar baru
 */
function buat_eksemplar(int $buku_id, int $jumlah, ?int $rak_id, string $sumber = 'beli', ?string $tanggal = null): array
{
    $terakhir = (int) db_val(
        "SELECT MAX(CAST(SUBSTRING_INDEX(kode_eksemplar, '-', -1) AS UNSIGNED)) FROM eksemplar WHERE buku_id = ?",
        [$buku_id]
    );
    $st = db()->prepare('INSERT INTO eksemplar (buku_id, rak_id, kode_eksemplar, sumber, tgl_pengadaan) VALUES (?, ?, ?, ?, ?)');
    $ids = [];
    for ($i = 1; $i <= $jumlah; $i++) {
        $st->execute([$buku_id, $rak_id, kode_eksemplar($buku_id, $terakhir + $i), $sumber, $tanggal ?? hari_ini()]);
        $ids[] = (int) db()->lastInsertId();
    }
    return $ids;
}

/**
 * Tambah ($id null) atau ubah buku. Saat tambah, eksemplar dibuat sebanyak $in['jumlah_eksemplar'].
 * $file_cover: elemen $_FILES (boleh null / UPLOAD_ERR_NO_FILE). $in['hapus_cover'] menghapus sampul.
 * Melempar RuntimeException berisi pesan untuk pengguna.
 */
function simpan_buku(array $in, ?array $file_cover, ?int $id = null): int
{
    $v = fn(string $k) => trim((string) ($in[$k] ?? ''));
    $lama = $id ? db_row('SELECT * FROM buku WHERE id = ?', [$id]) : null;
    if ($id && !$lama) {
        throw new RuntimeException('Buku tidak ditemukan.');
    }

    $isbn = preg_replace('/[\s\-]/', '', $v('isbn'));
    $data = [
        'isbn' => $isbn === '' ? null : $isbn,
        'judul' => $v('judul'),
        'penulis' => $v('penulis'),
        'penerbit_id' => $v('penerbit_id') === '' ? null : (int) $v('penerbit_id'),
        'kategori_id' => (int) $v('kategori_id'),
        'tahun_terbit' => $v('tahun_terbit') === '' ? null : $v('tahun_terbit'),
        'halaman' => $v('halaman') === '' ? null : $v('halaman'),
        'sinopsis' => $v('sinopsis') === '' ? null : $v('sinopsis'),
        'harga' => $v('harga') === '' ? '0' : $v('harga'),
    ];

    if ($data['judul'] === '') {
        throw new RuntimeException('Judul wajib diisi.');
    }
    if ($data['penulis'] === '') {
        throw new RuntimeException('Penulis wajib diisi.');
    }
    if (!db_val('SELECT 1 FROM kategori WHERE id = ?', [$data['kategori_id']])) {
        throw new RuntimeException('Kategori wajib dipilih.');
    }
    if ($data['penerbit_id'] !== null && !db_val('SELECT 1 FROM penerbit WHERE id = ?', [$data['penerbit_id']])) {
        throw new RuntimeException('Penerbit tidak valid.');
    }
    if ($data['isbn'] !== null && !preg_match('/^[0-9X]{10,13}$/i', $data['isbn'])) {
        throw new RuntimeException('ISBN harus 10 atau 13 digit.');
    }
    if ($data['isbn'] !== null && db_val('SELECT 1 FROM buku WHERE isbn = ? AND id <> ?', [$data['isbn'], (int) $id])) {
        throw new RuntimeException('ISBN sudah dipakai buku lain.');
    }
    if ($data['tahun_terbit'] !== null && (!ctype_digit($data['tahun_terbit']) || $data['tahun_terbit'] < 1000 || $data['tahun_terbit'] > (int) date('Y') + 1)) {
        throw new RuntimeException('Tahun terbit tidak valid.');
    }
    if ($data['halaman'] !== null && (!ctype_digit($data['halaman']) || $data['halaman'] > 30000)) {
        throw new RuntimeException('Jumlah halaman tidak valid.');
    }
    if (!ctype_digit($data['harga'])) {
        throw new RuntimeException('Harga harus angka 0 atau lebih.');
    }
    $jumlah = (int) ($in['jumlah_eksemplar'] ?? 0);
    if (!$id && ($jumlah < 1 || $jumlah > 50)) {
        throw new RuntimeException('Jumlah eksemplar 1–50.');
    }
    $rak_id = ($in['rak_id'] ?? '') === '' ? null : (int) $in['rak_id'];

    // Upload lebih dulu; jika DB gagal, file baru dihapus lagi.
    $cover = null;
    if ($file_cover && ($file_cover['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $cover = simpan_gambar($file_cover, 'cover', 2_000_000, ['image/jpeg', 'image/png', 'image/webp'], 600, 200);
    }
    if ($cover) {
        $data['foto_cover'] = $cover['file'];
        $data['foto_thumb'] = $cover['thumb'] ?? $cover['file'];
    } elseif ($lama && !empty($in['hapus_cover'])) {
        $data['foto_cover'] = $data['foto_thumb'] = null;
    }

    $db = db();
    $db->beginTransaction();
    try {
        $kolom = array_keys($data);
        if ($id) {
            $set = implode(', ', array_map(fn($k) => "$k = ?", $kolom));
            $db->prepare("UPDATE buku SET $set WHERE id = ?")->execute([...array_values($data), $id]);
        } else {
            $db->prepare('INSERT INTO buku (' . implode(', ', $kolom) . ') VALUES (' . implode(', ', array_fill(0, count($kolom), '?')) . ')')
                ->execute(array_values($data));
            $id = (int) $db->lastInsertId();
            buat_eksemplar($id, $jumlah, $rak_id, ($in['sumber'] ?? 'beli') === 'hibah' ? 'hibah' : 'beli');
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        if ($cover) {
            hapus_gambar('cover', $cover['file']);
        }
        throw $e;
    }

    // Sampul lama dibuang setelah data baru pasti tersimpan.
    if ($lama && array_key_exists('foto_cover', $data) && $lama['foto_cover']) {
        hapus_gambar('cover', $lama['foto_cover']);
    }
    return $id;
}

/** Tandai eksemplar rusak/hilang manual, atau kembalikan ke tersedia. Tidak berlaku untuk eksemplar dalam transaksi. */
function tandai_eksemplar(int $id, string $status): void
{
    if (!in_array($status, ['tersedia', 'rusak', 'hilang'], true)) {
        throw new RuntimeException('Status hanya bisa diubah ke tersedia, rusak, atau hilang.');
    }
    $sekarang = db_val('SELECT status FROM eksemplar WHERE id = ?', [$id]);
    if ($sekarang === false) {
        throw new RuntimeException('Eksemplar tidak ditemukan.');
    }
    if (in_array($sekarang, ['dipinjam', 'direservasi'], true)) {
        throw new RuntimeException('Eksemplar sedang dalam transaksi (dipinjam/direservasi).');
    }
    db()->prepare('UPDATE eksemplar SET status = ? WHERE id = ?')->execute([$status, $id]);
}

/** Hapus eksemplar yang belum pernah dipakai transaksi. */
function hapus_eksemplar(int $id): void
{
    if (db_val('SELECT 1 FROM peminjaman_detail WHERE eksemplar_id = ? UNION SELECT 1 FROM reservasi WHERE eksemplar_id = ? LIMIT 1', [$id, $id])) {
        throw new RuntimeException('Eksemplar punya riwayat transaksi; tandai rusak/hilang saja.');
    }
    if (in_array(db_val('SELECT status FROM eksemplar WHERE id = ?', [$id]), ['dipinjam', 'direservasi'], true)) {
        throw new RuntimeException('Eksemplar sedang dalam transaksi.');
    }
    db()->prepare('DELETE FROM eksemplar WHERE id = ?')->execute([$id]);
}
