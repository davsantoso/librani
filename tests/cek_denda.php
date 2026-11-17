<?php
reset_db();
$p = fx_petugas();
$a = fx_anggota();
$a2 = fx_anggota();
$b = fx_buku(3, ['harga' => 60000]);
$pid = simpan_peminjaman($a, [$b['eksemplar'][0], $b['eksemplar'][1]], $p, '2026-11-01');   // jatuh tempo 8 Nov
$d = array_column(db_all('SELECT id FROM peminjaman_detail WHERE peminjaman_id = ? ORDER BY id', [$pid]), 'id');
proses_kembali([['detail_id' => $d[0], 'kondisi' => 'baik'], ['detail_id' => $d[1], 'kondisi' => 'rusak']], $p, '2026-11-10');
$denda = array_map('intval', array_column(db_all('SELECT id FROM denda WHERE anggota_id = ? ORDER BY id', [$a]), 'id'));
sama('3 denda: 2 terlambat + 1 rusak', 3, count($denda));
cek('anggota terblokir sebelum bayar', syarat_pinjam($a, '2026-11-10') !== []);

$no = bayar_denda([$denda[0], $denda[1]], $p, '2026-11-10');
sama('nomor kuitansi', 'BYR-20261110-0001', $no);
sama('dua denda lunas dengan kuitansi sama', 2, (int) db_val("SELECT COUNT(*) FROM denda WHERE status = 'lunas' AND no_kuitansi = ?", [$no]));
sama('tgl bayar tercatat', '2026-11-10', db_val('SELECT tgl_bayar FROM denda WHERE id = ?', [$denda[0]]));
sama('petugas penerima tercatat', $p, (int) db_val('SELECT petugas_id FROM denda WHERE id = ?', [$denda[0]]));
harus_gagal('bayar ulang ditolak', fn() => bayar_denda([$denda[0]], $p, '2026-11-10'), 'belum lunas');
harus_gagal('tanpa pilihan ditolak', fn() => bayar_denda([], $p), 'minimal satu');

// Denda anggota lain untuk uji campur anggota
$b2 = fx_buku(1);
$pid2 = simpan_peminjaman($a2, $b2['eksemplar'], $p, '2026-11-01');
proses_kembali([['detail_id' => detail_id_dari($pid2), 'kondisi' => 'baik']], $p, '2026-11-09');
$denda_a2 = (int) db_val('SELECT id FROM denda WHERE anggota_id = ?', [$a2]);
harus_gagal('campur dua anggota ditolak', fn() => bayar_denda([$denda[2], $denda_a2], $p, '2026-11-10'), 'satu anggota');
sama('tidak ada yang berubah saat gagal', 'belum_lunas', db_val('SELECT status FROM denda WHERE id = ?', [$denda[2]]));

harus_gagal('bebaskan tanpa alasan', fn() => bebaskan_denda($denda[2], 1, ' '), 'Alasan');
bebaskan_denda($denda[2], 1, 'Buku rusak karena banjir');
sama('status dibebaskan', 'dibebaskan', db_val('SELECT status FROM denda WHERE id = ?', [$denda[2]]));
cek('alasan tercatat', str_contains((string) db_val('SELECT keterangan FROM denda WHERE id = ?', [$denda[2]]), 'banjir'));
harus_gagal('bebaskan yang sudah lunas ditolak', fn() => bebaskan_denda($denda[0], 1, 'x'), 'belum lunas');
sama('setelah lunas/dibebaskan boleh pinjam lagi', [], syarat_pinjam($a, '2026-11-10'));
