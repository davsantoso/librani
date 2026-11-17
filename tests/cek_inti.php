<?php
reset_db();
sama('e escape', '&lt;b&gt;&quot;', e('<b>"'));
sama('e null', '', e(null));
sama('rupiah', 'Rp3.000', rupiah(3000));
sama('rupiah besar', 'Rp1.250.000', rupiah(1250000));
sama('tgl', '13 Nov 2026', tgl('2026-11-13'));
sama('tgl null', '-', tgl(null));
sama('url', BASE_URL . '/modules/auth/login.php', url('modules/auth/login.php'));
sama('setting', '3', setting('maks_pinjam'));
cek('login username benar', coba_login('admin', 'admin123') !== null);
cek('login email benar', coba_login('admin@librani.test', 'admin123') !== null);
cek('password salah ditolak', coba_login('admin', 'salah') === null);
cek('user tidak ada ditolak', coba_login('tidakada', 'admin123') === null);
db()->exec("UPDATE users SET is_active=0 WHERE username='admin'");
cek('user nonaktif ditolak', coba_login('admin', 'admin123') === null);
sama('dashboard url', BASE_URL . '/modules/dashboard/index.php', dashboard_url('petugas'));

// ---- Pengaturan (Task 11)
reset_db();
simpan_setting(['denda_per_hari' => '2000']);
sama('setting tersimpan & cache segar', '2000', setting('denda_per_hari'));
simpan_setting(['nama_perpustakaan' => 'Perpustakaan Kota']);
sama('teks boleh', 'Perpustakaan Kota', setting('nama_perpustakaan'));
harus_gagal('nilai negatif ditolak', fn() => simpan_setting(['denda_per_hari' => '-1']), 'bilangan bulat');
harus_gagal('nilai huruf ditolak', fn() => simpan_setting(['lama_pinjam' => 'abc']), 'bilangan bulat');
harus_gagal('maks_pinjam 0 ditolak', fn() => simpan_setting(['maks_pinjam' => '0']), 'minimal 1');
harus_gagal('kunci tak dikenal ditolak', fn() => simpan_setting(['hack' => '1']), 'tidak dikenal');
harus_gagal('nama perpustakaan kosong ditolak', fn() => simpan_setting(['nama_perpustakaan' => ' ']), 'wajib');
sama('gagal tidak mengubah apa pun', '2000', (function () {
    try { simpan_setting(['denda_per_hari' => '5000', 'lama_pinjam' => 'x']); } catch (RuntimeException) {}
    unset($GLOBALS['_setting']);
    return setting('denda_per_hari');
})());
