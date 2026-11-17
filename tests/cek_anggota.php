<?php
reset_db();
$p = fx_petugas();
$a = simpan_register(data_register(), null);
sama('kode pertama', 'AGT-2026-0001', verifikasi_anggota($a, $p, '2026-11-17'));
sama('status aktif', 'aktif', db_val('SELECT status FROM anggota WHERE id = ?', [$a]));
sama('masa berlaku 365 hari', '2027-11-17', db_val('SELECT tgl_berlaku FROM anggota WHERE id = ?', [$a]));
sama('verified_by tercatat', $p, (int) db_val('SELECT verified_by FROM anggota WHERE id = ?', [$a]));
$a2 = simpan_register(data_register(), null);
sama('kode kedua', 'AGT-2026-0002', verifikasi_anggota($a2, $p, '2026-11-17'));
$a2b = simpan_register(data_register(), null);
sama('urutan reset per tahun', 'AGT-2027-0001', verifikasi_anggota($a2b, $p, '2027-01-02'));
harus_gagal('verifikasi dua kali', fn() => verifikasi_anggota($a, $p), 'menunggu');

$a3 = simpan_register(data_register(), null);
harus_gagal('tolak tanpa alasan', fn() => tolak_anggota($a3, $p, '  '), 'Alasan');
tolak_anggota($a3, $p, 'Foto KTP tidak jelas');
sama('status ditolak', 'ditolak', db_val('SELECT status FROM anggota WHERE id = ?', [$a3]));
sama('alasan tersimpan', 'Foto KTP tidak jelas', db_val('SELECT alasan_tolak FROM anggota WHERE id = ?', [$a3]));
harus_gagal('verifikasi anggota ditolak gagal', fn() => verifikasi_anggota($a3, $p), 'menunggu');

perpanjang_keanggotaan($a, '2026-12-01');
sama('perpanjang dari tgl_berlaku lama', '2028-11-16', db_val('SELECT tgl_berlaku FROM anggota WHERE id = ?', [$a]));
db()->prepare("UPDATE anggota SET tgl_berlaku = '2026-01-01', status = 'nonaktif' WHERE id = ?")->execute([$a2]);
perpanjang_keanggotaan($a2, '2026-11-17');
sama('perpanjang dari hari ini bila sudah lewat', '2027-11-17', db_val('SELECT tgl_berlaku FROM anggota WHERE id = ?', [$a2]));
sama('perpanjang mengaktifkan', 'aktif', db_val('SELECT status FROM anggota WHERE id = ?', [$a2]));
harus_gagal('perpanjang anggota ditolak gagal', fn() => perpanjang_keanggotaan($a3), 'belum');

// Ubah data anggota: uniknya tidak bentrok dengan diri sendiri
$d = data_register();
$a4 = simpan_register($d, null);
sama('validasi ubah tanpa password/username', [], validasi_register(['nama' => 'Nama Baru', 'username' => '', 'password' => ''] + $d, $a4));
ubah_anggota($a4, ['nama' => 'Nama Baru'] + $d, null);
sama('nama anggota berubah', 'Nama Baru', db_val('SELECT nama FROM anggota WHERE id = ?', [$a4]));
sama('nama user ikut berubah', 'Nama Baru', db_val('SELECT nama FROM users WHERE id = ?', [user_id_anggota($a4)]));
$d5 = data_register();
simpan_register($d5, null);
cek("ubah ke email anggota lain ditolak", in_array("Email sudah dipakai.", validasi_register(["email" => $d5["email"]] + $d, $a4), true));
cek("ubah ke no identitas anggota lain ditolak", in_array("No. identitas sudah terdaftar.", validasi_register(["no_identitas" => $d5["no_identitas"]] + $d, $a4), true));

// Fixture fx_anggota
$fx = fx_anggota(['tgl_berlaku' => '2026-01-01']);
sama('fx_anggota menimpa kolom', '2026-01-01', db_val('SELECT tgl_berlaku FROM anggota WHERE id = ?', [$fx]));
