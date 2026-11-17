<?php
reset_db();
$p = fx_petugas();
$a1 = fx_anggota();
$a2 = fx_anggota();
$a3 = fx_anggota();
$b = fx_buku(1);
$e = $b['eksemplar'][0];

harus_gagal('masih tersedia → tidak bisa reservasi', fn() => buat_reservasi($a2, $b['buku_id'], '2026-11-17'), 'tersedia');
$pid = simpan_peminjaman($a1, [$e], $p, '2026-11-17');
harus_gagal('peminjam sendiri tidak bisa reservasi', fn() => buat_reservasi($a1, $b['buku_id'], '2026-11-17'), 'sedang meminjam');
$r2 = buat_reservasi($a2, $b['buku_id'], '2026-11-17');
$r3 = buat_reservasi($a3, $b['buku_id'], '2026-11-18');
sama('status awal menunggu', 'menunggu', db_val('SELECT status FROM reservasi WHERE id = ?', [$r2]));
harus_gagal('reservasi ganda judul sama', fn() => buat_reservasi($a2, $b['buku_id'], '2026-11-18'), 'sudah');
sama('posisi antrean a3 = 2', 2, reservasi_anggota($a3)[0]['posisi']);
harus_gagal('perpanjang ditolak karena ada reservasi', fn() => perpanjang(detail_id_dari($pid), user_id_anggota($a1), '2026-11-20'), 'direservasi');

proses_kembali([['detail_id' => detail_id_dari($pid), 'kondisi' => 'baik']], $p, '2026-11-20');
sama('eksemplar direservasi', 'direservasi', db_val('SELECT status FROM eksemplar WHERE id = ?', [$e]));
sama('antrean pertama siap', 'siap', db_val('SELECT status FROM reservasi WHERE id = ?', [$r2]));
sama('eksemplar dikunci untuk pemesan', $e, (int) db_val('SELECT eksemplar_id FROM reservasi WHERE id = ?', [$r2]));
sama('batas ambil +2 hari', '2026-11-22', db_val('SELECT tgl_batas_ambil FROM reservasi WHERE id = ?', [$r2]));
sama('antrean kedua tetap menunggu', 'menunggu', db_val('SELECT status FROM reservasi WHERE id = ?', [$r3]));
harus_gagal('anggota lain tidak bisa pinjam eksemplar direservasi', fn() => simpan_peminjaman($a3, [$e], $p, '2026-11-21'), 'tidak tersedia');
sama('katalog: tersedia 0 saat direservasi', 0, (int) cari_katalog()['data'][0]['tersedia']);

sama('belum kadaluarsa pada batas ambil', 0, cek_reservasi_kadaluarsa('2026-11-22'));
sama('kadaluarsa 1 setelah batas', 1, cek_reservasi_kadaluarsa('2026-11-23'));
sama('status kadaluarsa', 'kadaluarsa', db_val('SELECT status FROM reservasi WHERE id = ?', [$r2]));
sama('pindah ke antrean kedua', 'siap', db_val('SELECT status FROM reservasi WHERE id = ?', [$r3]));
sama('batas ambil antrean kedua', '2026-11-25', db_val('SELECT tgl_batas_ambil FROM reservasi WHERE id = ?', [$r3]));
sama('eksemplar tetap direservasi', 'direservasi', db_val('SELECT status FROM eksemplar WHERE id = ?', [$e]));

simpan_peminjaman($a3, [$e], $p, '2026-11-23');
sama('reservasi diambil', 'diambil', db_val('SELECT status FROM reservasi WHERE id = ?', [$r3]));
sama('eksemplar dipinjam pemesan', 'dipinjam', db_val('SELECT status FROM eksemplar WHERE id = ?', [$e]));

// Batal: reservasi siap yang dibatalkan melepas eksemplar ke tersedia (tidak ada antrean lain)
reset_db();
$p = fx_petugas();
$a1 = fx_anggota();
$a2 = fx_anggota();
$b = fx_buku(1);
$pid = simpan_peminjaman($a1, $b['eksemplar'], $p, '2026-11-17');
$r = buat_reservasi($a2, $b['buku_id'], '2026-11-17');
harus_gagal('batal milik orang lain ditolak', fn() => batal_reservasi($r, $a1), 'tidak ditemukan');
proses_kembali([['detail_id' => detail_id_dari($pid), 'kondisi' => 'baik']], $p, '2026-11-18');
batal_reservasi($r, $a2);
sama('status batal', 'batal', db_val('SELECT status FROM reservasi WHERE id = ?', [$r]));
sama('eksemplar kembali tersedia', 'tersedia', db_val('SELECT status FROM eksemplar WHERE id = ?', [$b['eksemplar'][0]]));
harus_gagal('batal dua kali ditolak', fn() => batal_reservasi($r, $a2), 'tidak bisa dibatalkan');

// Kembali rusak tidak dialokasikan; diperbaiki (tandai tersedia) → dialokasikan ke antrean
$pid = simpan_peminjaman($a1, $b['eksemplar'], $p, '2026-11-19');
$r = buat_reservasi($a2, $b['buku_id'], '2026-11-19');
proses_kembali([['detail_id' => detail_id_dari($pid), 'kondisi' => 'rusak']], $p, '2026-11-20');
sama('kembali rusak tidak dialokasikan', 'menunggu', db_val('SELECT status FROM reservasi WHERE id = ?', [$r]));
tandai_eksemplar($b['eksemplar'][0], 'tersedia');
sama('setelah diperbaiki langsung untuk antrean', 'siap', db_val('SELECT status FROM reservasi WHERE id = ?', [$r]));

// Batas reservasi aktif per anggota (default 2)
$b2 = fx_buku(1);
$b3 = fx_buku(1);
simpan_peminjaman($a1, $b2['eksemplar'], $p, '2026-11-20');
db()->prepare("UPDATE eksemplar SET status = 'dipinjam' WHERE id = ?")->execute([$b3['eksemplar'][0]]);
buat_reservasi($a2, $b2['buku_id'], '2026-11-20');
harus_gagal('melebihi maks reservasi', fn() => buat_reservasi($a2, $b3['buku_id'], '2026-11-20'), 'Batas reservasi');

// ---- Review: race kedaluwarsa (pemanggilan basi tidak boleh mengalokasikan eksemplar yang sudah dipinjam)
reset_db();
$p = fx_petugas();
[$a1, $a2, $a3] = [fx_anggota(), fx_anggota(), fx_anggota()];
$b = fx_buku(1);
$e = $b['eksemplar'][0];
$pid = simpan_peminjaman($a1, [$e], $p, '2026-11-17');
$r2 = buat_reservasi($a2, $b['buku_id'], '2026-11-17');
$r3 = buat_reservasi($a3, $b['buku_id'], '2026-11-18');
proses_kembali([['detail_id' => detail_id_dari($pid), 'kondisi' => 'baik']], $p, '2026-11-20');   // r2 siap
simpan_peminjaman($a2, [$e], $p, '2026-11-21');                                                     // r2 diambil
sama('alokasi ke eksemplar yang dipinjam ditolak', false, alokasikan_reservasi($e, '2026-11-23'));
sama('eksemplar tetap dipinjam', 'dipinjam', db_val('SELECT status FROM eksemplar WHERE id = ?', [$e]));
sama('antrean berikutnya tetap menunggu', 'menunggu', db_val('SELECT status FROM reservasi WHERE id = ?', [$r3]));
sama('kadaluarsakan reservasi yang sudah diambil (basi) → tidak terjadi', false, kadaluarsakan_reservasi($r2, '2026-11-23'));
sama('status reservasi diambil tidak berubah', 'diambil', db_val('SELECT status FROM reservasi WHERE id = ?', [$r2]));
harus_gagal('anggota lain tidak bisa meminjam eksemplar yang sama', fn() => simpan_peminjaman($a3, [$e], $p, '2026-11-23'), 'tidak tersedia');
sama('hanya satu detail aktif untuk eksemplar', 1, (int) db_val("SELECT COUNT(*) FROM peminjaman_detail WHERE eksemplar_id = ? AND status = 'dipinjam'", [$e]));
