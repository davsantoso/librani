<?php
// Smoke test HTTP: login per role, buka setiap halaman, pastikan tidak ada error PHP
// dan role yang salah mendapat 403. Butuh Apache + database hasil `php database/seed.php`.
//   php tests/smoke.php
require __DIR__ . '/../config/config.php';

$akun = [
    'admin' => ['admin', 'admin123'],
    'petugas' => ['petugas1', 'petugas123'],
    'anggota' => ['anggota1', 'anggota123'],
];

// Halaman yang harus terbuka (200) per role. Task berikutnya menambah ke daftar ini.
$halaman = [
    'publik' => ['', 'modules/auth/login.php', 'modules/auth/register.php'],
    'admin' => ['modules/dashboard/index.php', 'modules/auth/profil.php'],
    'petugas' => ['modules/dashboard/index.php', 'modules/auth/profil.php'],
    'anggota' => ['modules/dashboard/index.php', 'modules/auth/profil.php'],
];

// Halaman yang harus ditolak (403) per role.
$terlarang = [
    'admin' => [],
    'petugas' => [],
    'anggota' => [],
];

// Form POST yang harus berhasil (302, tanpa error) per role: [halaman form, data].
$kirim = ['admin' => [], 'petugas' => [], 'anggota' => []];

// --- Master kategori, penerbit, rak (Task 5)
$n = substr((string) time(), -5);
foreach (['kategori', 'penerbit', 'rak'] as $m) {
    array_push($halaman['admin'], "modules/master/$m/index.php", "modules/master/$m/form.php");
    $halaman['petugas'][] = "modules/master/$m/index.php";
    $terlarang['petugas'][] = "modules/master/$m/form.php";
    $terlarang['anggota'][] = "modules/master/$m/index.php";
}
$kirim['admin'][] = ['modules/master/kategori/form.php', ['kode' => "S$n", 'nama' => "Smoke $n", 'deskripsi' => '']];
$kirim['admin'][] = ['modules/master/penerbit/form.php', ['nama' => "Penerbit Smoke $n", 'kota' => 'Surabaya', 'telepon' => '031-123456']];
$kirim['admin'][] = ['modules/master/rak/form.php', ['kode' => "S$n", 'nama' => "Rak Smoke $n", 'lantai' => '1']];

// --- User petugas (Task 6)
array_push($halaman['admin'], 'modules/master/users/index.php', 'modules/master/users/form.php', 'modules/master/users/form.php?id=1');
$terlarang['petugas'][] = 'modules/master/users/index.php';
$terlarang['anggota'][] = 'modules/master/users/form.php';
$kirim['admin'][] = ['modules/master/users/form.php', ['nama' => "Petugas Smoke $n", 'username' => "ptg$n", 'email' => "ptg$n@librani.test", 'role' => 'petugas', 'password' => 'petugas123', 'is_active' => '1']];

// ---------------------------------------------------------------------------

$ok = 0;
$gagal = 0;
function hasil(bool $lolos, string $pesan): void
{
    global $ok, $gagal;
    $lolos ? $ok++ : $gagal++;
    echo ($lolos ? '  OK     ' : '  GAGAL  ') . $pesan . PHP_EOL;
}

/** @return array{0:int,1:string,2:string} [status, body, location] */
function minta(CurlHandle $ch, string $path, ?array $post = null): array
{
    curl_setopt_array($ch, [
        CURLOPT_URL => BASE_URL . '/' . ltrim($path, '/'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_POST => $post !== null,
        CURLOPT_HTTPGET => $post === null,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw = (string) curl_exec($ch);
    $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $head = substr($raw, 0, $hs);
    preg_match('/^Location:\s*(.+)$/mi', $head, $m);
    return [curl_getinfo($ch, CURLINFO_RESPONSE_CODE), substr($raw, $hs), trim($m[1] ?? '')];
}

function sesi_baru(): CurlHandle
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_COOKIEFILE, ''); // cookie jar di memori
    return $ch;
}

function ada_error_php(string $body): ?string
{
    foreach (['Fatal error', 'Warning:', 'Notice:', 'Deprecated:', 'Parse error', 'Uncaught'] as $pola) {
        if (str_contains($body, $pola)) {
            return $pola;
        }
    }
    return null;
}

function token(string $html): string
{
    preg_match('/name="csrf" value="([0-9a-f]+)"/', $html, $m);
    return $m[1] ?? '';
}

function login(CurlHandle $ch, string $user, string $pass): bool
{
    [, $body] = minta($ch, 'modules/auth/login.php');
    [$st, , $loc] = minta($ch, 'modules/auth/login.php', ['csrf' => token($body), 'login' => $user, 'password' => $pass]);
    return $st === 302 && str_contains($loc, 'dashboard');
}

function cek_halaman(CurlHandle $ch, string $role, string $path): void
{
    [$st, $body] = minta($ch, $path);
    $err = ada_error_php($body);
    hasil($st === 200 && !$err, "[$role] $path → $st" . ($err ? " ($err)" : ''));
    if ($err && preg_match('/(Fatal error|Warning|Notice|Deprecated|Uncaught)[^<]*/', strip_tags($body), $m)) {
        echo '         ' . trim($m[0]) . PHP_EOL;
    }
}

// Publik
$ch = sesi_baru();
foreach ($halaman['publik'] as $p) {
    cek_halaman($ch, 'publik', $p);
}
[$st, , $loc] = minta($ch, 'modules/dashboard/index.php');
hasil($st === 302 && str_contains($loc, 'login'), "[publik] dashboard tanpa login → $st ke login");
[$st] = minta($ch, 'database/seed.php');
hasil($st === 404 || $st === 403, "[publik] database/seed.php tidak bisa diakses → $st");
[$st] = minta($ch, 'config/config.php');
hasil($st === 403 || $st === 404, "[publik] config/config.php tidak bisa diakses → $st");
hasil(!login($ch, 'admin', 'salah-password'), '[publik] login password salah ditolak');

// Register anggota baru lalu login: hanya melihat pesan menunggu verifikasi (FR-REG-03)
$ch = sesi_baru();
$n = substr((string) time(), -6);
[, $body] = minta($ch, "modules/auth/register.php");
[$st, , $loc] = minta($ch, "modules/auth/register.php", [
    "csrf" => token($body), "nama" => "Smoke $n", "no_identitas" => "SMK$n", "jenis_kelamin" => "L",
    "tgl_lahir" => "2001-01-01", "alamat" => "Jl. Smoke", "no_hp" => "08123$n", "email" => "smoke$n@contoh.id",
    "username" => "smoke$n", "password" => "smoke12345", "password2" => "smoke12345",
]);
hasil($st === 302 && str_contains($loc, "login"), "[publik] register anggota baru → $st");
hasil(login($ch, "smoke$n", "smoke12345"), "[menunggu] login anggota baru");
[$st, $body] = minta($ch, "modules/dashboard/index.php");
hasil($st === 200 && str_contains($body, "menunggu verifikasi"), "[menunggu] dashboard menampilkan pesan menunggu verifikasi");

// Per role
foreach ($akun as $role => [$u, $p]) {
    $ch = sesi_baru();
    hasil(login($ch, $u, $p), "[$role] login $u");
    foreach ($halaman[$role] as $path) {
        cek_halaman($ch, $role, $path);
    }
    foreach ($terlarang[$role] as $path) {
        [$st] = minta($ch, $path);
        hasil($st === 403, "[$role] $path ditolak → $st");
    }
    foreach ($kirim[$role] as [$path, $data]) {
        [, $body] = minta($ch, $path);
        [$st, $body, $loc] = minta($ch, $path, ['csrf' => token($body)] + $data);
        $err = ada_error_php($body);
        hasil($st === 302 && !$err, "[$role] POST $path → $st" . ($err ? " ($err)" : '') . ($st === 200 ? ' (validasi gagal?)' : ''));
    }
    // Logout lalu halaman terproteksi harus kembali ke login
    [, $body] = minta($ch, 'modules/dashboard/index.php');
    minta($ch, 'modules/auth/logout.php', ['csrf' => token($body)]);
    [$st] = minta($ch, 'modules/dashboard/index.php');
    hasil($st === 302, "[$role] setelah logout dashboard → $st");
}

echo PHP_EOL . "Smoke selesai: $ok OK, $gagal GAGAL" . PHP_EOL;
exit($gagal > 0 ? 1 : 0);
