<?php
reset_db();
$fiksi = insert('kategori', ['kode' => 'FIK', 'nama' => 'Novel Fiksi']);
$sej = insert('kategori', ['kode' => 'SEJ', 'nama' => 'Sejarah']);
$b1 = fx_buku(3, ['judul' => 'Laskar Pelangi', 'penulis' => 'Andrea Hirata', 'kategori_id' => $fiksi, 'isbn' => '9789793062792']);
$b2 = fx_buku(1, ['judul' => 'Bumi Manusia', 'penulis' => 'Pramoedya Ananta Toer', 'kategori_id' => $fiksi]);
$b3 = fx_buku(2, ['judul' => 'Sejarah Indonesia Modern', 'penulis' => 'M.C. Ricklefs', 'kategori_id' => $sej]);
tandai_eksemplar($b1['eksemplar'][0], 'rusak');
db()->prepare("UPDATE eksemplar SET status = 'dipinjam' WHERE id = ?")->execute([$b1['eksemplar'][1]]);

$h = cari_katalog();
sama('semua buku aktif', 3, $h['total']);
$per = array_column($h['data'], null, 'judul');
sama('tersedia hanya status tersedia', 1, (int) $per['Laskar Pelangi']['tersedia']);
sama('total eksemplar tidak menghitung rusak/hilang', 2, (int) $per['Laskar Pelangi']['total']);
sama('cari judul sebagian', ['Laskar Pelangi'], array_column(cari_katalog('pelangi')['data'], 'judul'));
sama('cari penulis', ['Bumi Manusia'], array_column(cari_katalog('pramoedya')['data'], 'judul'));
sama('cari ISBN', ['Laskar Pelangi'], array_column(cari_katalog('9789793062792')['data'], 'judul'));
sama('cari nama kategori', ['Sejarah Indonesia Modern'], array_column(cari_katalog('sejarah')['data'], 'judul'));
sama('filter kategori', 2, cari_katalog('', $fiksi)['total']);
sama('paginasi per halaman', 2, count(cari_katalog('', null, 1, 2)['data']));
sama('paginasi halaman 2', 1, count(cari_katalog('', null, 2, 2)['data']));
db()->prepare('UPDATE buku SET is_active = 0 WHERE id = ?')->execute([$b2['buku_id']]);
sama('buku nonaktif tidak muncul', 0, cari_katalog('Bumi')['total']);
sama('karakter LIKE di-escape', 0, cari_katalog('%')['total']);
