<?php
reset_db();
$p = fx_petugas();
$a = fx_anggota();
$a2 = fx_anggota();
$menunggu = simpan_register(data_register(), null);
$b = fx_buku(4, ['judul' => 'Populer']);
$b2 = fx_buku(2, ['judul' => 'Kurang Populer']);
$hari_ini = '2026-11-17';
simpan_peminjaman($a, [$b['eksemplar'][0]], $p, $hari_ini);                        // pinjam hari ini
$telat = simpan_peminjaman($a2, [$b['eksemplar'][1], $b2['eksemplar'][0]], $p, '2026-11-01'); // jatuh tempo 8 Nov → terlambat
$selesai = simpan_peminjaman($a, [$b['eksemplar'][2]], $p, '2026-11-05');
proses_kembali([['detail_id' => detail_id_dari($selesai), 'kondisi' => 'baik']], $p, '2026-11-14'); // terlambat 2 hari → Rp2.000

$s = statistik_sirkulasi($hari_ini);
sama('total judul', 2, $s['total_judul']);
sama('total eksemplar', 6, $s['total_eksemplar']);
sama('sedang dipinjam', 3, $s['dipinjam']);
sama('pinjam hari ini', 1, $s['pinjam_hari_ini']);
sama('eksemplar terlambat', 2, $s['terlambat']);
sama('denda belum lunas', 2000, $s['denda_belum_lunas']);
sama('menunggu verifikasi', 1, $s['menunggu_verifikasi']);

$g = grafik_30_hari($hari_ini);
sama('grafik 30 baris', 30, count($g));
sama('hari terakhir = hari ini', ['tgl' => $hari_ini, 'jumlah' => 1], end($g));
sama('hari tanpa pinjaman bernilai 0', 0, $g[0]['jumlah']);
sama('1 Nov ada 1 transaksi', 1, array_column($g, 'jumlah', 'tgl')['2026-11-01']);

$pop = buku_populer('2026-11-01', '2026-11-30', 5);
sama('buku terpopuler', 'Populer', $pop[0]['judul']);
sama('jumlah dipinjam', 3, (int) $pop[0]['jumlah']);

$r = ringkasan_anggota($a2, $hari_ini);
sama('pinjaman aktif anggota', 2, count($r['aktif']));
cek('ditandai terlambat', $r['aktif'][0]['terlambat'] === true);
sama('sisa kuota 3-2', 1, $r['sisa_kuota']);
sama('denda anggota 0', 0, $r['denda_belum_lunas']);
sama('denda anggota lain', 2000, ringkasan_anggota($a, $hari_ini)['denda_belum_lunas']);

$jt = jatuh_tempo_dan_terlambat('2026-11-08');
sama('jatuh tempo hari ini', 2, count($jt['hari_ini']));
sama('terlambat per 17 Nov', 2, count(jatuh_tempo_dan_terlambat($hari_ini)['terlambat']));
