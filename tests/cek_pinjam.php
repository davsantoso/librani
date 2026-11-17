<?php
reset_db();
$p = fx_petugas();
$a = fx_anggota();
$b = fx_buku(5);
$e = $b['eksemplar'];

sama('syarat anggota aktif terpenuhi', [], syarat_pinjam($a, '2026-11-17'));
$pid = simpan_peminjaman($a, [$e[0], $e[1], $e[2]], $p, '2026-11-17');
sama('nomor', 'PJM-20261117-0001', db_val('SELECT no_transaksi FROM peminjaman WHERE id = ?', [$pid]));
sama('status header aktif', 'aktif', db_val('SELECT status FROM peminjaman WHERE id = ?', [$pid]));
sama('tgl pinjam', '2026-11-17', db_val('SELECT tgl_pinjam FROM peminjaman WHERE id = ?', [$pid]));
sama('3 detail', 3, (int) db_val('SELECT COUNT(*) FROM peminjaman_detail WHERE peminjaman_id = ?', [$pid]));
sama('jatuh tempo +7', '2026-11-24', db_val('SELECT DISTINCT tgl_jatuh_tempo FROM peminjaman_detail WHERE peminjaman_id = ?', [$pid]));
sama('denda_per_hari disalin', 1000, (int) db_val('SELECT DISTINCT denda_per_hari FROM peminjaman_detail WHERE peminjaman_id = ?', [$pid]));
sama('eksemplar dipinjam', 3, (int) db_val("SELECT COUNT(*) FROM eksemplar WHERE status = 'dipinjam'"));

harus_gagal('buku ke-4 melebihi batas', fn() => simpan_peminjaman($a, [$e[3]], $p, '2026-11-17'), 'batas');
$a2 = fx_anggota();
harus_gagal('eksemplar sudah dipinjam (form basi)', fn() => simpan_peminjaman($a2, [$e[3], $e[0]], $p, '2026-11-17'), 'tidak tersedia');
sama('rollback: e[3] tetap tersedia', 'tersedia', db_val('SELECT status FROM eksemplar WHERE id = ?', [$e[3]]));
sama('rollback: tidak ada header baru', 1, (int) db_val('SELECT COUNT(*) FROM peminjaman'));
harus_gagal('eksemplar duplikat', fn() => simpan_peminjaman($a2, [$e[3], $e[3]], $p, '2026-11-17'), 'dua kali');
harus_gagal('tanpa eksemplar', fn() => simpan_peminjaman($a2, [], $p, '2026-11-17'), 'minimal satu');
harus_gagal('eksemplar tidak ada', fn() => simpan_peminjaman($a2, [999999], $p, '2026-11-17'), 'tidak ditemukan');
tandai_eksemplar($e[4], 'rusak');
harus_gagal('eksemplar rusak ditolak', fn() => simpan_peminjaman($a2, [$e[4]], $p, '2026-11-17'), 'tidak tersedia');
tandai_eksemplar($e[4], 'tersedia');

sama('nomor kedua hari sama', 'PJM-20261117-0002', db_val('SELECT no_transaksi FROM peminjaman WHERE id = ?', [simpan_peminjaman($a2, [$e[3]], $p, '2026-11-17')]));
sama('nomor reset hari berikut', 'PJM-20261118-0001', db_val('SELECT no_transaksi FROM peminjaman WHERE id = ?', [simpan_peminjaman($a2, [$e[4]], $p, '2026-11-18')]));

sama('belum terlambat pada jatuh tempo', 0, pinjaman_terlambat($a, '2026-11-24'));
sama('terlambat sehari setelahnya', 3, pinjaman_terlambat($a, '2026-11-25'));
cek('terlambat memblokir', in_array('Masih ada pinjaman terlambat.', syarat_pinjam($a, '2026-11-25'), true));
$a3 = fx_anggota(['tgl_berlaku' => '2026-11-01']);
cek('kedaluwarsa memblokir', in_array('Masa berlaku keanggotaan habis.', syarat_pinjam($a3, '2026-11-17'), true));
$a4 = simpan_register(data_register(), null);
cek('anggota menunggu memblokir', in_array('Anggota belum aktif.', syarat_pinjam($a4, '2026-11-17'), true));
harus_gagal('anggota menunggu tidak bisa pinjam', fn() => simpan_peminjaman($a4, [fx_buku(1)['eksemplar'][0]], $p, '2026-11-17'), 'belum aktif');

// Denda belum lunas memblokir
$a5 = fx_anggota();
$pj = insert('peminjaman', ['no_transaksi' => 'PJM-LAMA', 'anggota_id' => $a5, 'petugas_id' => $p, 'tgl_pinjam' => '2026-10-01', 'status' => 'selesai']);
$eks_lama = fx_buku(1)['eksemplar'][0];
$det = insert('peminjaman_detail', ['peminjaman_id' => $pj, 'eksemplar_id' => $eks_lama, 'tgl_jatuh_tempo' => '2026-10-08', 'denda_per_hari' => 1000, 'status' => 'kembali']);
$kmb = insert('pengembalian', ['no_pengembalian' => 'KMB-LAMA', 'peminjaman_detail_id' => $det, 'tgl_kembali' => '2026-10-11', 'hari_terlambat' => 3, 'petugas_id' => $p]);
insert('denda', ['pengembalian_id' => $kmb, 'anggota_id' => $a5, 'jenis' => 'terlambat', 'jumlah' => 3000]);
sama('denda belum lunas memblokir', ['Masih ada denda belum lunas (Rp3.000).'], syarat_pinjam($a5, '2026-11-17'));
