<?php
// Mengisi ulang database `librani` dengan data contoh. HANYA lewat terminal:
//   php database/seed.php
// PERINGATAN: semua data di database librani akan dihapus.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config/config.php';
(new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS))
    ->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
require __DIR__ . '/../includes/init.php';

$db = db();
$db->exec('DROP DATABASE `' . DB_NAME . '`');
$db->exec('CREATE DATABASE `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec('USE `' . DB_NAME . '`');
unset($GLOBALS['_setting']);
jalankan_sql_file(__DIR__ . '/schema.sql');
echo "Skema dimuat.\n";

function seed_user(string $nama, string $username, string $password, string $role): int
{
    db()->prepare('INSERT INTO users (nama, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)')
        ->execute([$nama, $username, "$username@librani.test", password_hash($password, PASSWORD_DEFAULT), $role]);
    return (int) db()->lastInsertId();
}

// ---- Akun demo ----
$petugas1 = seed_user('Siti Rahmawati', 'petugas1', 'petugas123', 'petugas');
$uAnggota = seed_user('Budi Santoso', 'anggota1', 'anggota123', 'anggota');
db()->prepare(
    "INSERT INTO anggota (user_id, kode_anggota, no_identitas, nama, jenis_kelamin, tgl_lahir, alamat, no_hp,
        status, tgl_daftar, tgl_berlaku, verified_by)
     VALUES (?, 'AGT-2026-0001', '3578010101900001', 'Budi Santoso', 'L', '1990-01-01', 'Jl. Kenanga No. 5', '081234567890',
        'aktif', ?, ?, ?)"
)->execute([$uAnggota, hari_ini(), tambah_hari(hari_ini(), (int) setting('masa_berlaku_anggota')), $petugas1]);

echo "Akun demo: admin/admin123, petugas1/petugas123, anggota1/anggota123\n";

// ---- Master sementara (diganti data buku asli di Task 22) ----
$kat = (function () {
    db()->exec("INSERT INTO kategori (kode, nama) VALUES ('NOV', 'Novel & Fiksi')");
    return (int) db()->lastInsertId();
})();
db()->exec("INSERT INTO penerbit (nama, kota) VALUES ('Bentang Pustaka', 'Yogyakarta')");
$pen = (int) db()->lastInsertId();
db()->exec("INSERT INTO rak (kode, nama, lantai) VALUES ('A1', 'Rak Fiksi A1', 1)");
$rak = (int) db()->lastInsertId();
simpan_buku([
    'judul' => 'Laskar Pelangi', 'penulis' => 'Andrea Hirata', 'penerbit_id' => $pen, 'kategori_id' => $kat,
    'tahun_terbit' => '2005', 'halaman' => '529', 'harga' => '89000', 'jumlah_eksemplar' => 3, 'rak_id' => $rak,
    'sinopsis' => 'Kisah sepuluh anak Belitung yang bersekolah di SD Muhammadiyah yang hampir ditutup.',
], null);
echo "Data master contoh dimuat.\n";
echo "Selesai.\n";
