<?php
$mime_cover = ['image/jpeg', 'image/png', 'image/webp'];

$r = simpan_gambar(fixture_upload(fixture_png(3000, 1500)), 'cover', 2_000_000, $mime_cover, 600, 200);
sama('lebar utama 600', 600, getimagesize(UPLOAD_DIR . "/cover/{$r['file']}")[0]);
sama('lebar thumb 200', 200, getimagesize(UPLOAD_DIR . "/cover/thumb/{$r['thumb']}")[0]);
cek('nama acak 32 hex', (bool) preg_match('/^[0-9a-f]{32}\.(jpg|png|webp)$/', $r['file']));
hapus_gambar('cover', $r['file']);
cek('hapus_gambar menghapus utama + thumb', !is_file(UPLOAD_DIR . "/cover/{$r['file']}") && !is_file(UPLOAD_DIR . "/cover/thumb/{$r['thumb']}"));

$kecil = simpan_gambar(fixture_upload(fixture_png(300, 450)), 'cover', 2_000_000, $mime_cover, 600, 200);
sama('gambar kecil tidak diperbesar', 300, getimagesize(UPLOAD_DIR . "/cover/{$kecil['file']}")[0]);
hapus_gambar('cover', $kecil['file']);

harus_gagal('php disamarkan jpg ditolak', fn() => simpan_gambar(fixture_file('palsu.jpg', '<?php echo 1;'), 'cover', 2_000_000, $mime_cover, 600, 200), 'format');
harus_gagal('kebesaran ditolak', fn() => simpan_gambar(fixture_upload(fixture_png(10, 10), 3_000_000), 'cover', 2_000_000, $mime_cover), '2 MB');
harus_gagal('png ditolak bila hanya jpg', fn() => simpan_gambar(fixture_upload(fixture_png(10, 10)), 'anggota', 1_000_000, ['image/jpeg']), 'format');
harus_gagal('error upload ditolak', fn() => simpan_gambar(['tmp_name' => '', 'error' => UPLOAD_ERR_PARTIAL, 'size' => 0], 'cover', 2_000_000, $mime_cover), 'gagal');
harus_gagal('folder tidak dikenal ditolak', fn() => simpan_gambar(fixture_upload(fixture_png(10, 10)), '../config', 2_000_000, $mime_cover));
