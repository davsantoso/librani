<?php
// Smoke test HTTP: login per role, buka setiap halaman, pastikan tidak ada error PHP
// dan role yang salah mendapat 403. Jalankan tepat setelah `php database/seed.php` (form POST di sini menambah data).
//   php tests/smoke.php
require __DIR__ . '/../config/config.php';

$akun = [
    'petugas' => ['petugas1', 'petugas123'],
    'admin' => ['admin', 'admin123'],
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
// --- Buku (Task 7)
foreach (['admin', 'petugas'] as $r) {
    array_push($halaman[$r], 'modules/master/buku/index.php', 'modules/master/buku/form.php', 'modules/master/buku/form.php?id=1', 'modules/master/buku/detail.php?id=1');
    $kirim[$r][] = ['modules/master/buku/form.php', ['judul' => "Buku Smoke $r $n", 'penulis' => 'Penulis Smoke', 'kategori_id' => '1', 'jumlah_eksemplar' => '2', 'harga' => '50000']];
}
array_push($terlarang['anggota'], 'modules/master/buku/index.php', 'modules/master/buku/form.php');

// --- Eksemplar (Task 8)
foreach (['admin', 'petugas'] as $r) {
    array_push($halaman[$r], 'modules/master/eksemplar/index.php', 'modules/master/eksemplar/index.php?status=tersedia&buku_id=1',
        'modules/master/eksemplar/form.php', 'modules/master/eksemplar/form.php?buku_id=1', 'modules/master/eksemplar/form.php?id=1');
}
$kirim['petugas'][] = ['modules/master/eksemplar/form.php?buku_id=1', ['buku_id' => '1', 'jumlah' => '1', 'rak_id' => '', 'kondisi' => 'baik', 'sumber' => 'hibah', 'tgl_pengadaan' => date('Y-m-d')]];
$kirim['petugas'][] = ['modules/master/eksemplar/index.php', ['id' => '1', 'status' => 'rusak'], 'modules/master/eksemplar/tandai.php'];
$kirim['petugas'][] = ['modules/master/eksemplar/index.php', ['id' => '1', 'status' => 'tersedia'], 'modules/master/eksemplar/tandai.php'];
$terlarang['anggota'][] = 'modules/master/eksemplar/index.php';

// --- Anggota (Task 9)
foreach (['admin', 'petugas'] as $r) {
    array_push($halaman[$r], 'modules/master/anggota/index.php', 'modules/master/anggota/index.php?status=menunggu',
        'modules/master/anggota/form.php', 'modules/master/anggota/form.php?id=1', 'modules/master/anggota/detail.php?id=1', 'modules/master/anggota/kartu.php?id=1');
}
$halaman['anggota'][] = 'modules/master/anggota/kartu.php';
array_push($terlarang['anggota'], 'modules/master/anggota/index.php', 'modules/master/anggota/detail.php?id=1');
$kirim['petugas'][] = ['modules/master/anggota/form.php', [
    'nama' => "Anggota Loket $n", 'no_identitas' => "LKT$n", 'jenis_kelamin' => 'P', 'tgl_lahir' => '1999-09-09',
    'alamat' => 'Jl. Loket', 'no_hp' => "08777$n", 'email' => "loket$n@contoh.id", 'username' => "loket$n", 'password' => 'loket12345',
]];
$kirim['petugas'][] = ['modules/master/anggota/detail.php?id=1', ['id' => '1', 'aksi' => 'perpanjang'], 'modules/master/anggota/verifikasi.php'];

// --- Katalog (Task 10)
array_push($halaman['publik'], '?q=laskar', '?kategori=1&hal=1', '?q=%25', 'modules/katalog/detail.php?id=1');
array_push($halaman['anggota'], 'modules/katalog/index.php', 'modules/katalog/index.php?q=andrea', 'modules/katalog/detail.php?id=1');
$terlarang['petugas'][] = 'modules/katalog/index.php';

// --- Pengaturan (Task 11)
$halaman['admin'][] = 'modules/pengaturan/index.php';
$terlarang['petugas'][] = 'modules/pengaturan/index.php';
$terlarang['anggota'][] = 'modules/pengaturan/index.php';
$kirim['admin'][] = ['modules/pengaturan/index.php', ['nilai' => ['denda_per_hari' => '1000', 'nama_perpustakaan' => 'Perpustakaan Umum Librani']]];

// --- Peminjaman (Task 13). anggota1 = anggota id 1; eksemplar 2 & 3 = BK0001-02/03 dari seeder.
$kirim['petugas'][] = ['modules/transaksi/peminjaman/form.php', ['anggota_id' => '1', 'eksemplar_ids' => ['2', '3']]];
array_push($halaman['petugas'], 'modules/transaksi/peminjaman/index.php', 'modules/transaksi/peminjaman/index.php?status=terlambat',
    'modules/transaksi/peminjaman/form.php', 'modules/transaksi/peminjaman/form.php?anggota=AGT-2026-0001',
    'modules/transaksi/peminjaman/detail.php?id=1', 'modules/transaksi/peminjaman/bukti.php?id=1',
    'modules/transaksi/peminjaman/cari_anggota.php?q=budi', 'modules/transaksi/peminjaman/cari_eksemplar.php?kode=bk0001-04&anggota_id=1');
array_push($halaman['admin'], 'modules/transaksi/peminjaman/index.php', 'modules/transaksi/peminjaman/detail.php?id=1');
$terlarang['admin'][] = 'modules/transaksi/peminjaman/form.php';
$terlarang['anggota'][] = 'modules/transaksi/peminjaman/index.php';

// --- Pengembalian (Task 14): kembalikan detail 3 (detail pertama peminjaman di atas; 1-2 milik transaksi seeder).
$kmb = 'KMB-' . date('Ymd') . '-0001';
$kirim['petugas'][] = ['modules/transaksi/pengembalian/form.php?q=AGT-2026-0001', ['q' => 'AGT-2026-0001', 'kembali' => ['3' => '1'], 'kondisi' => ['3' => 'baik']]];
array_push($halaman['petugas'], 'modules/transaksi/pengembalian/index.php', 'modules/transaksi/pengembalian/form.php',
    'modules/transaksi/pengembalian/form.php?q=AGT-2026-0001', 'modules/transaksi/pengembalian/form.php?q=PJM-' . date('Ymd') . '-0001',
    'modules/transaksi/pengembalian/form.php?q=tidak-ada', "modules/transaksi/pengembalian/bukti.php?no=$kmb");
array_push($halaman['admin'], 'modules/transaksi/pengembalian/index.php', "modules/transaksi/pengembalian/bukti.php?no=$kmb");
$terlarang['admin'][] = 'modules/transaksi/pengembalian/form.php';
$terlarang['anggota'][] = 'modules/transaksi/pengembalian/index.php';

// --- Denda (Task 15): seeder membuat 2 denda (id 1 & 2) untuk anggota2 / AGT-2026-0002.
$byr = 'BYR-' . date('Ymd') . '-0001';
$kirim['petugas'][] = ['modules/transaksi/denda/index.php?anggota=AGT-2026-0002', ['anggota' => 'AGT-2026-0002', 'denda_ids' => ['1']], 'modules/transaksi/denda/bayar.php'];
$kirim['admin'][] = ['modules/transaksi/denda/index.php', ['id' => '2', 'alasan' => 'Uji smoke'], 'modules/transaksi/denda/bebaskan.php'];
array_push($halaman['petugas'], 'modules/transaksi/denda/index.php', 'modules/transaksi/denda/index.php?status=semua',
    'modules/transaksi/denda/index.php?anggota=AGT-2026-0002', 'modules/transaksi/denda/index.php?anggota=TIDAK-ADA', "modules/transaksi/denda/kuitansi.php?no=$byr");
array_push($halaman['admin'], 'modules/transaksi/denda/index.php?status=dibebaskan', "modules/transaksi/denda/kuitansi.php?no=$byr");
$halaman['anggota'][] = 'modules/anggota/denda.php';
$terlarang['petugas'][] = 'modules/transaksi/denda/bebaskan.php';
$terlarang['anggota'][] = 'modules/transaksi/denda/index.php';

// --- Dashboard (Task 16): dashboard tiap role sudah dicek di atas; tambah halaman pinjaman anggota.
$halaman['anggota'][] = 'modules/anggota/pinjaman.php';
$terlarang['petugas'][] = 'modules/anggota/pinjaman.php';

// --- Laporan (Task 17): 7 laporan x mode layar & cetak, dengan dan tanpa filter
$daftar_laporan = ['peminjaman', 'pengembalian', 'denda', 'keterlambatan', 'populer', 'koleksi', 'anggota'];
foreach (['admin', 'petugas'] as $r) {
    $halaman[$r][] = 'modules/laporan/index.php';
    foreach ($daftar_laporan as $l) {
        array_push($halaman[$r], "modules/laporan/$l.php", "modules/laporan/$l.php?mode=cetak");
    }
}
array_push($halaman['admin'],
    'modules/laporan/peminjaman.php?dari=2026-01-01&sampai=2026-12-31&anggota_id=1&kategori_id=1&status=terlambat',
    'modules/laporan/pengembalian.php?ketepatan=terlambat&dari=2026-01-01',
    'modules/laporan/denda.php?status=lunas&anggota_id=2&mode=cetak',
    'modules/laporan/keterlambatan.php?min_terlambat=3&kategori_id=1',
    'modules/laporan/koleksi.php?status=dipinjam&rak_id=1',
    'modules/laporan/anggota.php?periode_daftar=1&status=aktif&dari=x');
$terlarang['anggota'][] = 'modules/laporan/index.php';
$terlarang['anggota'][] = 'modules/laporan/denda.php?mode=cetak';

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
[$st, $body] = minta($ch, "modules/katalog/index.php");
hasil($st === 200 && str_contains($body, "Laskar Pelangi"), "[menunggu] katalog anggota tetap bisa dibuka");

// Per role
foreach ($akun as $role => [$u, $p]) {
    $ch = sesi_baru();
    hasil(login($ch, $u, $p), "[$role] login $u");
    // [halaman asal token, data, (opsional) tujuan POST bila berbeda dari halaman asal]
    foreach ($kirim[$role] as $k) {
        [$asal, $data] = $k;
        $tujuan = $k[2] ?? $asal;
        [, $body] = minta($ch, $asal);
        [$st, $body] = minta($ch, $tujuan, ['csrf' => token($body)] + $data);
        $err = ada_error_php($body);
        hasil($st === 302 && !$err, "[$role] POST $tujuan → $st" . ($err ? " ($err)" : '') . ($st === 200 ? ' (validasi gagal?)' : ''));
    }
    foreach ($halaman[$role] as $path) {
        cek_halaman($ch, $role, $path);
    }
    foreach ($terlarang[$role] as $path) {
        [$st] = minta($ch, $path);
        hasil($st === 403, "[$role] $path ditolak → $st");
    }
    // Logout lalu halaman terproteksi harus kembali ke login
    [, $body] = minta($ch, 'modules/dashboard/index.php');
    minta($ch, 'modules/auth/logout.php', ['csrf' => token($body)]);
    [$st] = minta($ch, 'modules/dashboard/index.php');
    hasil($st === 302, "[$role] setelah logout dashboard → $st");
}

echo PHP_EOL . "Smoke selesai: $ok OK, $gagal GAGAL" . PHP_EOL;
exit($gagal > 0 ? 1 : 0);
