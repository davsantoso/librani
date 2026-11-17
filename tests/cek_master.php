<?php
reset_db();
$k = insert('kategori', ['kode' => 'FIK', 'nama' => 'Fiksi']);
sama('tanpa relasi dihapus', 'dihapus', hapus_atau_nonaktifkan('kategori', $k, [['buku', 'kategori_id']]));
sama('baris benar-benar hilang', 0, (int) db_val('SELECT COUNT(*) FROM kategori WHERE id = ?', [$k]));

$k2 = insert('kategori', ['kode' => 'SEJ', 'nama' => 'Sejarah']);
fx_buku(1, ['kategori_id' => $k2]);
sama('dengan relasi dinonaktifkan', 'dinonaktifkan', hapus_atau_nonaktifkan('kategori', $k2, [['buku', 'kategori_id']]));
sama('baris masih ada', 1, (int) db_val('SELECT COUNT(*) FROM kategori WHERE id = ?', [$k2]));
sama('is_active jadi 0', 0, (int) db_val('SELECT is_active FROM kategori WHERE id = ?', [$k2]));

$r = insert('rak', ['kode' => 'A1', 'nama' => 'Rak A1']);
sama('rak tanpa eksemplar dihapus', 'dihapus', hapus_atau_nonaktifkan('rak', $r, [['eksemplar', 'rak_id']]));
harus_gagal('tabel di luar whitelist', fn() => hapus_atau_nonaktifkan('denda', 1, []));
harus_gagal('relasi di luar whitelist', fn() => hapus_atau_nonaktifkan('kategori', 1, [['buku; DROP TABLE x', 'kategori_id']]));

// ---- User petugas (Task 6)
$data_user = fn(array $o = []) => $o + ['nama' => 'Petugas Baru', 'username' => 'petugas_baru', 'email' => 'pb@librani.test', 'role' => 'petugas', 'password' => 'petugas123'];
$uid = simpan_user($data_user());
sama('user petugas dibuat', 'petugas', db_val('SELECT role FROM users WHERE id = ?', [$uid]));
harus_gagal('role anggota ditolak', fn() => simpan_user($data_user(['username' => 'x1', 'email' => 'x1@a.id', 'role' => 'anggota'])), 'Role tidak valid');
harus_gagal('username duplikat ditolak', fn() => simpan_user($data_user(['email' => 'lain@a.id'])), 'Username sudah dipakai');
harus_gagal('email duplikat ditolak', fn() => simpan_user($data_user(['username' => 'lain'])), 'Email sudah dipakai');
harus_gagal('password wajib saat tambah', fn() => simpan_user($data_user(['username' => 'xx2', 'email' => 'x2@a.id', 'password' => ''])), 'Password minimal 8');
$hash = db_val('SELECT password_hash FROM users WHERE id = ?', [$uid]);
simpan_user($data_user(['nama' => 'Petugas Ganti Nama', 'password' => '']), $uid);
sama('ubah tanpa password tidak mengubah hash', $hash, db_val('SELECT password_hash FROM users WHERE id = ?', [$uid]));
sama('nama berubah', 'Petugas Ganti Nama', db_val('SELECT nama FROM users WHERE id = ?', [$uid]));
harus_gagal('tidak bisa ubah user anggota lewat simpan_user', function () use ($data_user) {
    $a = simpan_register(data_register(), null);
    simpan_user($data_user(['username' => 'zz', 'email' => 'zz@a.id']), (int) db_val('SELECT user_id FROM anggota WHERE id = ?', [$a]));
}, 'tidak ditemukan');
