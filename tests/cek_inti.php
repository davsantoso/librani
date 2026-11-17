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
