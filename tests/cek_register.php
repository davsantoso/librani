<?php
reset_db();
$in = data_register();
sama('valid tanpa error', [], validasi_register($in));
$id = simpan_register($in, null);
sama('status menunggu', 'menunggu', db_val('SELECT status FROM anggota WHERE id = ?', [$id]));
sama('role user anggota', 'anggota', db_val('SELECT u.role FROM users u JOIN anggota a ON a.user_id = u.id WHERE a.id = ?', [$id]));
cek('kode anggota belum dibuat', db_val('SELECT kode_anggota FROM anggota WHERE id = ?', [$id]) === null);
cek('password di-hash', password_verify('rahasia123', db_val('SELECT u.password_hash FROM users u JOIN anggota a ON a.user_id = u.id WHERE a.id = ?', [$id])));

$err = validasi_register($in);
cek('username duplikat ditolak', in_array('Username sudah dipakai.', $err, true));
cek('email duplikat ditolak', in_array('Email sudah dipakai.', $err, true));
cek('no identitas duplikat ditolak', in_array('No. identitas sudah terdaftar.', $err, true));
cek('password pendek ditolak', in_array('Password minimal 8 karakter.', validasi_register(data_register(['password' => '1234567'])), true));
cek('nama kosong ditolak', in_array('Nama wajib diisi.', validasi_register(data_register(['nama' => '  '])), true));
cek('email tidak valid ditolak', in_array('Format email tidak valid.', validasi_register(data_register(['email' => 'bukan-email'])), true));
cek('jenis kelamin tidak valid ditolak', in_array('Jenis kelamin tidak valid.', validasi_register(data_register(['jenis_kelamin' => 'X'])), true));
cek('tanggal lahir tidak valid ditolak', in_array('Tanggal lahir tidak valid.', validasi_register(data_register(['tgl_lahir' => '2026-02-31'])), true));

harus_gagal('ganti password lama salah', fn() => ganti_password(1, 'salah', 'baru12345'), 'lama');
harus_gagal('ganti password baru pendek', fn() => ganti_password(1, 'admin123', 'pendek'), 'minimal 8');
ganti_password(1, 'admin123', 'baru12345');
cek('password baru berlaku', coba_login('admin', 'baru12345') !== null);
