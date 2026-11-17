<?php
// Export database `librani` (struktur + data) ke database/librani.sql untuk diimpor lewat phpMyAdmin hosting.
//   php database/seed.php && php database/export.php
// Baris "sandbox mode" dari mysqldump MariaDB dibuang karena ditolak MySQL.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../config/config.php';
date_default_timezone_set('Asia/Jakarta');

$mysqldump = getenv('MYSQLDUMP') ?: (is_file('C:/xampp/mysql/bin/mysqldump.exe') ? 'C:/xampp/mysql/bin/mysqldump.exe' : 'mysqldump');
$cmd = escapeshellarg($mysqldump) . ' --skip-comments --skip-dump-date --default-character-set=utf8mb4'
    . ' -h ' . escapeshellarg(DB_HOST) . ' -u ' . escapeshellarg(DB_USER)
    . (DB_PASS !== '' ? ' -p' . escapeshellarg(DB_PASS) : '') . ' ' . escapeshellarg(DB_NAME);
exec($cmd, $baris, $kode);
if ($kode !== 0 || !$baris) {
    fwrite(STDERR, "mysqldump gagal (kode $kode). Set variabel MYSQLDUMP ke lokasi mysqldump jika perlu.\n");
    exit(1);
}
$baris = array_filter($baris, fn($b) => !str_starts_with($b, '/*M!999999'));
$sql = "-- Librani: struktur + data contoh. Impor ke database kosong.\n"
    . '-- Dibuat ' . date('Y-m-d H:i') . " dengan database/export.php. Akun demo: admin/admin123, petugas1/petugas123, anggota1/anggota123\n\n"
    . str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', implode("\n", $baris)) . "\n";
file_put_contents(__DIR__ . '/librani.sql', $sql);
echo 'database/librani.sql ditulis (' . round(strlen($sql) / 1024) . " KB).\n";
