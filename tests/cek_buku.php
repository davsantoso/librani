<?php
reset_db();
sama('kode eksemplar', 'BK0001-02', kode_eksemplar(1, 2));
sama('kode eksemplar id besar', 'BK0123-10', kode_eksemplar(123, 10));

$b = simpan_buku(data_buku(['jumlah_eksemplar' => 3]), null);
$k = 'BK' . str_pad((string) $b, 4, '0', STR_PAD_LEFT);
sama('3 eksemplar dibuat', ["$k-01", "$k-02", "$k-03"], array_column(db_all('SELECT kode_eksemplar FROM eksemplar WHERE buku_id = ? ORDER BY id', [$b]), 'kode_eksemplar'));
sama('eksemplar baru tersedia', 3, (int) db_val("SELECT COUNT(*) FROM eksemplar WHERE buku_id = ? AND status = 'tersedia'", [$b]));
sama('buat_eksemplar mengembalikan 2 id', 2, count(buat_eksemplar($b, 2, null)));
sama('melanjutkan urutan', "$k-05", db_val('SELECT MAX(kode_eksemplar) FROM eksemplar WHERE buku_id = ?', [$b]));
sama('ubah buku tidak menambah eksemplar', 5, (function () use ($b) {
    simpan_buku(data_buku(['judul' => 'Judul Baru', 'jumlah_eksemplar' => 9]), null, $b);
    return (int) db_val('SELECT COUNT(*) FROM eksemplar WHERE buku_id = ?', [$b]);
})());
sama('judul berubah', 'Judul Baru', db_val('SELECT judul FROM buku WHERE id = ?', [$b]));

simpan_buku(data_buku(['isbn' => '978-979-000-0001']), null);
harus_gagal('ISBN duplikat', fn() => simpan_buku(data_buku(['isbn' => '9789790000001']), null), 'ISBN');
$tanpa_isbn = simpan_buku(data_buku(['isbn' => '']), null);
cek('ISBN kosong disimpan NULL', db_val('SELECT isbn FROM buku WHERE id = ?', [$tanpa_isbn]) === null);
simpan_buku(data_buku(['isbn' => '']), null);
cek('dua buku tanpa ISBN boleh', true);
harus_gagal('judul wajib', fn() => simpan_buku(data_buku(['judul' => ' ']), null), 'Judul');
harus_gagal('kategori wajib valid', fn() => simpan_buku(data_buku(['kategori_id' => 99999]), null), 'Kategori');
harus_gagal('jumlah eksemplar 0 ditolak', fn() => simpan_buku(data_buku(['jumlah_eksemplar' => 0]), null), 'eksemplar');
harus_gagal('tahun tidak valid', fn() => simpan_buku(data_buku(['tahun_terbit' => '20x5']), null), 'Tahun');
harus_gagal('harga negatif', fn() => simpan_buku(data_buku(['harga' => '-5']), null), 'Harga');

cek('placeholder berisi judul ter-escape', str_contains(cover_html(['judul' => '<A>', 'foto_thumb' => null, 'foto_cover' => null]), '&lt;A&gt;'));
cek('cover dengan thumb memakai img lazy', str_contains(cover_html(['judul' => 'X', 'foto_thumb' => 'abc.jpg', 'foto_cover' => 'abc.jpg']), 'loading="lazy"'));

// Upload cover + ganti cover menghapus file lama
$id = simpan_buku(data_buku(), fixture_upload_png());
$lama = db_val('SELECT foto_cover FROM buku WHERE id = ?', [$id]);
cek('cover tersimpan', is_file(UPLOAD_DIR . "/cover/$lama") && is_file(UPLOAD_DIR . "/cover/thumb/$lama"));
simpan_buku(data_buku(), fixture_upload_png(), $id);
$baru = db_val('SELECT foto_cover FROM buku WHERE id = ?', [$id]);
cek('file lama terhapus', !is_file(UPLOAD_DIR . "/cover/$lama") && !is_file(UPLOAD_DIR . "/cover/thumb/$lama"));
cek('file baru ada', $baru !== $lama && is_file(UPLOAD_DIR . "/cover/$baru"));
simpan_buku(data_buku(['hapus_cover' => '1']), null, $id);
cek('hapus cover: kolom NULL dan file hilang', db_val('SELECT foto_cover FROM buku WHERE id = ?', [$id]) === null && !is_file(UPLOAD_DIR . "/cover/$baru"));
harus_gagal('upload cover palsu ditolak', fn() => simpan_buku(data_buku(), fixture_file('x.jpg', '<?php')), 'Format');

// ---- Eksemplar (Task 8)
reset_db();
$b = fx_buku(3);
[$e1, $e2, $e3] = $b['eksemplar'];
tandai_eksemplar($e1, 'rusak');
sama('tersedia → rusak', 'rusak', db_val('SELECT status FROM eksemplar WHERE id = ?', [$e1]));
tandai_eksemplar($e1, 'tersedia');
sama('rusak → tersedia', 'tersedia', db_val('SELECT status FROM eksemplar WHERE id = ?', [$e1]));
db()->prepare("UPDATE eksemplar SET status = 'dipinjam' WHERE id = ?")->execute([$e2]);
harus_gagal('dipinjam → hilang ditolak', fn() => tandai_eksemplar($e2, 'hilang'), 'sedang dalam transaksi');
harus_gagal('status tidak dikenal ditolak', fn() => tandai_eksemplar($e1, 'dipinjam'), 'Status');
hapus_eksemplar($e3);
sama('hapus eksemplar tanpa riwayat', 0, (int) db_val('SELECT COUNT(*) FROM eksemplar WHERE id = ?', [$e3]));
$a = simpan_register(data_register(), null);
$pj = insert('peminjaman', ['no_transaksi' => 'PJM-X', 'anggota_id' => $a, 'petugas_id' => 1, 'tgl_pinjam' => '2026-11-01', 'status' => 'selesai']);
insert('peminjaman_detail', ['peminjaman_id' => $pj, 'eksemplar_id' => $e1, 'tgl_jatuh_tempo' => '2026-11-08', 'denda_per_hari' => 1000, 'status' => 'kembali']);
harus_gagal('hapus eksemplar dengan riwayat ditolak', fn() => hapus_eksemplar($e1), 'riwayat');
