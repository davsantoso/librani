<?php
sama('contoh PRD', ['hari' => 3, 'denda' => 3000], hitung_denda_terlambat('2026-11-10', '2026-11-13', 1000));
sama('tepat jatuh tempo', ['hari' => 0, 'denda' => 0], hitung_denda_terlambat('2026-11-10', '2026-11-10', 1000));
sama('lebih awal', ['hari' => 0, 'denda' => 0], hitung_denda_terlambat('2026-11-10', '2026-11-05', 1000));
sama('lintas bulan', ['hari' => 25, 'denda' => 50000], hitung_denda_terlambat('2026-11-20', '2026-12-15', 2000));

reset_db();
$p = fx_petugas();
$a = fx_anggota();
$b = fx_buku(3, ['harga' => 80000]);
$pid = simpan_peminjaman($a, $b['eksemplar'], $p, '2026-11-03');   // jatuh tempo 10 Nov
$d = array_column(db_all('SELECT id FROM peminjaman_detail WHERE peminjaman_id = ? ORDER BY id', [$pid]), 'id');

$pra = pratinjau_kembali([$d[0]], '2026-11-13');
sama('pratinjau hari terlambat', 3, $pra[0]['hari']);
sama('pratinjau denda terlambat', 3000, $pra[0]['denda_terlambat']);
sama('pratinjau denda rusak', 40000, $pra[0]['denda_rusak']);

simpan_setting(['denda_per_hari' => '5000']);                       // aturan berubah setelah pinjam
$no = proses_kembali([['detail_id' => $d[0], 'kondisi' => 'baik']], $p, '2026-11-13');
sama('nomor KMB', 'KMB-20261113-0001', $no);
sama('hari terlambat tersimpan', 3, (int) db_val('SELECT hari_terlambat FROM pengembalian WHERE peminjaman_detail_id = ?', [$d[0]]));
sama('denda pakai tarif lama', 3000, (int) db_val("SELECT jumlah FROM denda WHERE jenis = 'terlambat'"));
sama('denda milik anggota', $a, (int) db_val("SELECT anggota_id FROM denda WHERE jenis = 'terlambat'"));
sama('denda belum lunas', 'belum_lunas', db_val("SELECT status FROM denda WHERE jenis = 'terlambat'"));
sama('detail kembali', 'kembali', db_val('SELECT status FROM peminjaman_detail WHERE id = ?', [$d[0]]));
sama('header masih aktif (sebagian)', 'aktif', db_val('SELECT status FROM peminjaman WHERE id = ?', [$pid]));
harus_gagal('kembali dua kali', fn() => proses_kembali([['detail_id' => $d[0], 'kondisi' => 'baik']], $p, '2026-11-13'), 'sudah dikembalikan');
harus_gagal('kondisi tidak valid', fn() => proses_kembali([['detail_id' => $d[1], 'kondisi' => 'lecek']], $p, '2026-11-13'), 'Kondisi');
harus_gagal('tanpa item', fn() => proses_kembali([], $p, '2026-11-13'), 'minimal satu');
harus_gagal('sebelum tanggal pinjam', fn() => proses_kembali([['detail_id' => $d[1], 'kondisi' => 'baik']], $p, '2026-11-01'), 'sebelum tanggal pinjam');

$no2 = proses_kembali([['detail_id' => $d[1], 'kondisi' => 'rusak'], ['detail_id' => $d[2], 'kondisi' => 'hilang']], $p, '2026-11-10');
sama('satu nomor KMB untuk beberapa eksemplar', 2, (int) db_val('SELECT COUNT(*) FROM pengembalian WHERE no_pengembalian = ?', [$no2]));
sama('nomor KMB kedua (hari lain)', 'KMB-20261110-0001', $no2);
sama('header selesai', 'selesai', db_val('SELECT status FROM peminjaman WHERE id = ?', [$pid]));
sama('denda rusak 50%', 40000, (int) db_val("SELECT jumlah FROM denda WHERE jenis = 'rusak'"));
sama('denda hilang 100%', 80000, (int) db_val("SELECT jumlah FROM denda WHERE jenis = 'hilang'"));
sama('tepat waktu tanpa denda terlambat', 1, (int) db_val("SELECT COUNT(*) FROM denda WHERE jenis = 'terlambat'"));
sama('status eksemplar', ['tersedia', 'rusak', 'hilang'], array_column(db_all('SELECT status FROM eksemplar ORDER BY id'), 'status'));
sama('konsistensi: tidak ada dipinjam tanpa detail aktif', 0, (int) db_val(
    "SELECT COUNT(*) FROM eksemplar e WHERE e.status = 'dipinjam'
     AND NOT EXISTS (SELECT 1 FROM peminjaman_detail d WHERE d.eksemplar_id = e.id AND d.status = 'dipinjam')"
));

// Buku tanpa harga: rusak/hilang tidak menimbulkan denda harga
reset_db();
$p = fx_petugas();
$a = fx_anggota();
$b = fx_buku(1, ['harga' => 0]);
$pid = simpan_peminjaman($a, $b['eksemplar'], $p, '2026-11-03');
proses_kembali([['detail_id' => detail_id_dari($pid), 'kondisi' => 'hilang']], $p, '2026-11-05');
sama('harga 0: tanpa denda hilang', 0, (int) db_val('SELECT COUNT(*) FROM denda'));

// ---- Pencarian pinjaman aktif untuk form pengembalian
reset_db();
$p = fx_petugas();
$a = fx_anggota();
$b = fx_buku(3);
$pj1 = simpan_peminjaman($a, [$b['eksemplar'][0]], $p, '2026-11-01');
$pj2 = simpan_peminjaman($a, [$b['eksemplar'][1], $b['eksemplar'][2]], $p, '2026-11-02');
$no1 = db_val('SELECT no_transaksi FROM peminjaman WHERE id = ?', [$pj1]);
$kode_a = db_val('SELECT kode_anggota FROM anggota WHERE id = ?', [$a]);
$kode_e = db_val('SELECT kode_eksemplar FROM eksemplar WHERE id = ?', [$b['eksemplar'][2]]);
sama('cari no transaksi → item transaksi itu', 1, count(cari_pinjaman_aktif($no1)));
sama('cari kode anggota → semua pinjaman aktif', 3, count(cari_pinjaman_aktif(strtolower($kode_a))));
sama('cari kode eksemplar → semua pinjaman aktif anggotanya', 3, count(cari_pinjaman_aktif($kode_e)));
proses_kembali([['detail_id' => detail_id_dari($pj1), 'kondisi' => 'baik']], $p, '2026-11-05');
sama('yang sudah kembali tidak muncul', 2, count(cari_pinjaman_aktif($kode_a)));
sama('kosong → []', [], cari_pinjaman_aktif('  '));

// ---- Review: scan satu kode eksemplar hanya mencentang eksemplar itu
$hasil = cari_pinjaman_aktif($kode_e);
sama('scan eksemplar: hanya 1 yang dipilih', [$kode_e], array_column(array_filter($hasil, fn($i) => $i['dipilih']), 'kode_eksemplar'));
sama('cari kode anggota: semua dipilih', count(cari_pinjaman_aktif($kode_a)), count(array_filter(cari_pinjaman_aktif($kode_a), fn($i) => $i['dipilih'])));
sama('cari no transaksi: semua item transaksi dipilih', [true], array_values(array_unique(array_column(cari_pinjaman_aktif((string) db_val('SELECT no_transaksi FROM peminjaman WHERE id = ?', [$pj2])), 'dipilih'))));
