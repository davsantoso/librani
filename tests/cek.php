<?php
// Jalankan: php tests/cek.php [filter]
// Contoh  : php tests/cek.php pinjam  -> hanya tests/cek_pinjam.php
putenv('LIBRANI_DB=librani_test');
require __DIR__ . '/../config/config.php';

// Database uji harus ada sebelum db() pertama kali tersambung.
(new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS))
    ->exec('CREATE DATABASE IF NOT EXISTS librani_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/lib.php';

$filter = $argv[1] ?? '';
foreach (glob(__DIR__ . '/cek_*.php') as $file) {
    $nama = basename($file, '.php');
    if ($filter !== '' && $nama !== "cek_$filter") {
        continue;
    }
    echo "== $nama" . PHP_EOL;
    try {
        require $file;
    } catch (Throwable $e) {
        cek("$nama berhenti: " . get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine(), false);
    }
}

$h = $GLOBALS['hasil_tes'];
echo PHP_EOL . "Selesai: {$h['ok']} OK, {$h['gagal']} GAGAL" . PHP_EOL;
exit($h['gagal'] > 0 || $h['ok'] === 0 ? 1 : 0);
