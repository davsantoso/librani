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
