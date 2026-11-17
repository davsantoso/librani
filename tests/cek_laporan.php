<?php
reset_db();
$p = fx_petugas();
$a = fx_anggota(['nama' => 'Ani']);
$a2 = fx_anggota(['nama' => 'Bayu']);
$fiksi = insert('kategori', ['kode' => 'FIK', 'nama' => 'Fiksi']);
$b = fx_buku(3, ['judul' => 'Buku A', 'kategori_id' => $fiksi]);
$b2 = fx_buku(2, ['judul' => 'Buku B']);

$pj1 = simpan_peminjaman($a, [$b['eksemplar'][0]], $p, '2026-11-01');                       // jatuh tempo 8 Nov
$pj2 = simpan_peminjaman($a2, [$b['eksemplar'][1], $b2['eksemplar'][0]], $p, '2026-11-03'); // jatuh tempo 10 Nov
$pj3 = simpan_peminjaman($a2, [$b2['eksemplar'][1]], $p, '2026-10-20');                     // di luar periode, jatuh tempo 27 Okt
proses_kembali([['detail_id' => detail_id_dari($pj1), 'kondisi' => 'baik']], $p, '2026-11-11');   // terlambat 3 hari → 3000
proses_kembali([['detail_id' => detail_id_dari($pj3), 'kondisi' => 'baik']], $p, '2026-10-29');   // terlambat 2 hari → 2000
bayar_denda([(int) db_val("SELECT id FROM denda WHERE jumlah = 3000")], $p, '2026-11-11');
$nov = ['dari' => '2026-11-01', 'sampai' => '2026-11-30'];

$r = laporan_peminjaman($nov);
sama('RPT-01 baris per eksemplar dalam periode', 3, count($r['baris']));
sama('RPT-01 total transaksi', 2, $r['total']['Jumlah transaksi']);
sama('RPT-01 filter anggota', 2, count(laporan_peminjaman($nov + ['anggota_id' => $a2])['baris']));
sama('RPT-01 filter kategori', 2, count(laporan_peminjaman($nov + ['kategori_id' => $fiksi])['baris']));
sama('RPT-01 filter status dipinjam', 2, count(laporan_peminjaman($nov + ['status' => 'dipinjam'])['baris']));

$r = laporan_pengembalian(['dari' => '2026-10-01', 'sampai' => '2026-11-30', 'ketepatan' => 'terlambat']);
sama('RPT-02 terlambat 2 baris', 2, count($r['baris']));
sama('RPT-02 hanya November', 1, count(laporan_pengembalian($nov)['baris']));
sama('RPT-02 total denda', 3000, laporan_pengembalian($nov)['total']['Total denda']);
sama('RPT-02 tepat waktu 0 baris', 0, count(laporan_pengembalian($nov + ['ketepatan' => 'tepat'])['baris']));

$r = laporan_denda(['dari' => '2026-10-01', 'sampai' => '2026-11-30']);
sama('RPT-03 total lunas', 3000, $r['total']['Lunas']);
sama('RPT-03 total belum lunas', 2000, $r['total']['Belum lunas']);
sama('RPT-03 filter status', 1, count(laporan_denda(['dari' => '2026-10-01', 'sampai' => '2026-11-30', 'status' => 'belum_lunas'])['baris']));

$r = laporan_keterlambatan(['tanggal' => '2026-11-17']);
sama('RPT-04 dua eksemplar terlambat (pj2)', 2, count($r['baris']));
sama('RPT-04 estimasi denda 7 hari x 1000 x 2', 14000, $r['total']['Estimasi denda']);
sama('RPT-04 filter min terlambat', 0, count(laporan_keterlambatan(['tanggal' => '2026-11-17', 'min_terlambat' => 8])['baris']));

$r = laporan_populer(['dari' => '2026-10-01', 'sampai' => '2026-11-30']);
sama('RPT-05 dua blok', 2, count($r['blok']));
sama('RPT-05 buku teratas', 'Buku A', $r['blok'][0]['baris'][0][1]);
sama('RPT-05 anggota teraktif', 'Bayu', $r['blok'][1]['baris'][0][1]);

$r = laporan_koleksi([]);
$per_judul = array_column($r['baris'], null, 0);
sama('RPT-06 Buku A: 2 dipinjam? (1 kembali)', 1, $per_judul['Buku A'][3]);
sama('RPT-06 total eksemplar', 5, $r['total']['Total eksemplar']);
sama('RPT-06 filter status dipinjam', 2, count(laporan_koleksi(['status' => 'dipinjam'])['baris']));

$r = laporan_anggota([]);
sama('RPT-07 semua anggota', 2, count($r['baris']));
sama('RPT-07 jumlah eksemplar dipinjam Bayu (2 + 1)', 3, array_column($r['baris'], 5, 1)['Bayu']);
sama('RPT-07 filter status menunggu', 0, count(laporan_anggota(['status' => 'menunggu'])['baris']));

$f = filter_laporan(['dari' => '2026-13-45', 'sampai' => 'x']);
cek('filter tanggal tidak valid → bulan berjalan', $f['dari'] === date('Y-m-01') && $f['sampai'] === date('Y-m-t'));
$f = filter_laporan(['dari' => '2026-11-30', 'sampai' => '2026-11-01']);
sama('filter dari > sampai ditukar', ['2026-11-01', '2026-11-30'], [$f['dari'], $f['sampai']]);
