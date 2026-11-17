<?php
reset_db();
$p = fx_petugas();
$a = fx_anggota();
$a2 = fx_anggota();
$b = fx_buku(2);
$pid = simpan_peminjaman($a, [$b['eksemplar'][0]], $p, '2026-11-17');   // jatuh tempo 24 Nov
$d = detail_id_dari($pid);
$u = user_id_anggota($a);

sama('bisa diperpanjang', null, alasan_tidak_bisa_perpanjang($d, $u, '2026-11-20'));
sama('jatuh tempo baru 24 Nov + 7', '2026-12-01', perpanjang($d, $u, '2026-11-20'));
sama('jatuh tempo tersimpan', '2026-12-01', db_val('SELECT tgl_jatuh_tempo FROM peminjaman_detail WHERE id = ?', [$d]));
sama('jumlah perpanjangan', 1, (int) db_val('SELECT jumlah_perpanjangan FROM peminjaman_detail WHERE id = ?', [$d]));
sama('riwayat tersimpan', ['jatuh_tempo_lama' => '2026-11-24', 'jatuh_tempo_baru' => '2026-12-01', 'diajukan_oleh' => $u], db_row('SELECT jatuh_tempo_lama, jatuh_tempo_baru, diajukan_oleh FROM perpanjangan WHERE peminjaman_detail_id = ?', [$d]));
harus_gagal('kedua kali ditolak (batas 1)', fn() => perpanjang($d, $u, '2026-11-21'), 'batas perpanjangan');

$pid2 = simpan_peminjaman($a2, [$b['eksemplar'][1]], $p, '2026-11-01');   // jatuh tempo 8 Nov
harus_gagal('sudah terlambat ditolak', fn() => perpanjang(detail_id_dari($pid2), $p, '2026-11-10'), 'terlambat');
harus_gagal('anggota lain tidak bisa memperpanjang', fn() => perpanjang(detail_id_dari($pid2), $u, '2026-11-05'), 'Bukan pinjaman Anda');
sama('petugas boleh memperpanjang milik anggota', '2026-11-15', perpanjang(detail_id_dari($pid2), $p, '2026-11-05'));

// Reservasi anggota lain untuk judul yang sama memblokir
$b3 = fx_buku(1);
$pid3 = simpan_peminjaman($a, [$b3['eksemplar'][0]], $p, '2026-11-17');
insert('reservasi', ['anggota_id' => $a2, 'buku_id' => $b3['buku_id'], 'tgl_reservasi' => '2026-11-18 10:00:00', 'status' => 'menunggu']);
harus_gagal('ada reservasi anggota lain', fn() => perpanjang(detail_id_dari($pid3), $u, '2026-11-19'), 'direservasi');

harus_gagal('yang sudah kembali ditolak', function () use ($pid, $p, $d, $u) {
    proses_kembali([['detail_id' => $d, 'kondisi' => 'baik']], $p, '2026-11-22');
    perpanjang($d, $u, '2026-11-22');
}, 'dikembalikan');
