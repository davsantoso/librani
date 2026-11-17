<?php
// Helper tes sederhana berbasis assert. Dipakai oleh tests/cek.php.

$GLOBALS['hasil_tes'] = ['ok' => 0, 'gagal' => 0];

function cek(string $nama, bool $ok): void
{
    $GLOBALS['hasil_tes'][$ok ? 'ok' : 'gagal']++;
    echo ($ok ? "  OK     " : "  GAGAL  ") . $nama . PHP_EOL;
}

function sama(string $nama, mixed $harapan, mixed $aktual): void
{
    $ok = $harapan === $aktual;
    cek($nama, $ok);
    if (!$ok) {
        echo '         harapan: ' . var_export($harapan, true) . PHP_EOL;
        echo '         aktual : ' . var_export($aktual, true) . PHP_EOL;
    }
}

function harus_gagal(string $nama, callable $fn, string $pesan_mengandung = ''): void
{
    try {
        $fn();
    } catch (Throwable $e) {
        $ok = $pesan_mengandung === '' || stripos($e->getMessage(), $pesan_mengandung) !== false;
        cek($nama, $ok);
        if (!$ok) {
            echo "         pesan: {$e->getMessage()}" . PHP_EOL;
        }
        return;
    }
    cek($nama . ' (tidak melempar exception)', false);
}

/** Kosongkan database uji lalu muat ulang database/schema.sql. */
function reset_db(): void
{
    if (DB_NAME !== 'librani_test') {
        throw new LogicException('reset_db() hanya boleh dipakai pada librani_test, bukan ' . DB_NAME);
    }
    $pdo = db();
    $pdo->exec('DROP DATABASE IF EXISTS librani_test');
    $pdo->exec('CREATE DATABASE librani_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE librani_test');
    unset($GLOBALS['_setting']);
    jalankan_sql_file(__DIR__ . '/../database/schema.sql');
}


// ---------- Fixture ----------

/** File PNG sementara berukuran $w x $h (dibuat dengan GD). */
function fixture_png(int $w, int $h): string
{
    $img = imagecreatetruecolor($w, $h);
    imagefill($img, 0, 0, imagecolorallocate($img, 60, 90, 130));
    $path = tempnam(sys_get_temp_dir(), 'lbr') . '.png';
    imagepng($img, $path);
    return $path;
}

/** Array mirip $_FILES dari file sementara; 'tes' => true agar simpan_gambar memakai rename(). */
function fixture_upload(string $path, ?int $size = null): array
{
    return ['tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => $size ?? filesize($path), 'tes' => true];
}

function fixture_file(string $nama, string $isi): array
{
    $path = sys_get_temp_dir() . '/' . uniqid('lbr') . '_' . $nama;
    file_put_contents($path, $isi);
    return fixture_upload($path);
}

function fixture_upload_png(int $w = 800, int $h = 1200): array
{
    return fixture_upload(fixture_png($w, $h));
}

function data_register(array $o = []): array
{
    static $n = 0;
    $n++;
    return $o + [
        'nama' => "Anggota Uji $n",
        'no_identitas' => '35780100000' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
        'jenis_kelamin' => 'P',
        'tgl_lahir' => '2000-05-17',
        'alamat' => 'Jl. Uji No. ' . $n,
        'no_hp' => '0812000' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
        'email' => "uji$n@contoh.id",
        'username' => "uji$n",
        'password' => 'rahasia123',
    ];
}

/** INSERT sederhana untuk fixture. Nama tabel/kolom berasal dari kode tes, bukan input. */
function insert(string $tabel, array $data): int
{
    $kolom = implode(', ', array_keys($data));
    $tanda = implode(', ', array_fill(0, count($data), '?'));
    db()->prepare("INSERT INTO $tabel ($kolom) VALUES ($tanda)")->execute(array_values($data));
    return (int) db()->lastInsertId();
}

/** Kategori baru dengan kode unik. */
function fx_kategori(): int
{
    static $n = 0;
    $n++;
    return insert("kategori", ["kode" => "K$n" . mt_rand(100, 999), "nama" => "Kategori $n"]);
}

function data_buku(array $o = []): array
{
    static $n = 0;
    $n++;
    $o["kategori_id"] ??= fx_kategori();
    return $o + ["judul" => "Buku Uji $n", "penulis" => "Penulis $n", "jumlah_eksemplar" => 1, "harga" => 0];
}

/** Buku + $jumlah eksemplar tersedia (lewat simpan_buku). @return array{buku_id:int, eksemplar:int[]} */
function fx_buku(int $jumlah_eksemplar = 1, array $o = []): array
{
    $buku_id = simpan_buku(data_buku(["jumlah_eksemplar" => $jumlah_eksemplar] + $o), null);
    $eks = array_map("intval", array_column(db_all("SELECT id FROM eksemplar WHERE buku_id = ? ORDER BY id", [$buku_id]), "id"));
    return ["buku_id" => $buku_id, "eksemplar" => $eks];
}

function fx_petugas(): int
{
    static $n = 0;
    $n++;
    return insert('users', ['nama' => "Petugas $n", 'username' => "fxpetugas$n", 'email' => "fxpetugas$n@librani.test",
        'password_hash' => password_hash('petugas123', PASSWORD_DEFAULT), 'role' => 'petugas']);
}

/** Anggota aktif (register + verifikasi). $o menimpa kolom tabel anggota, mis. ['tgl_berlaku' => '2026-01-01']. */
function fx_anggota(array $o = [], string $tgl_verifikasi = '2026-01-05'): int
{
    static $petugas = null;
    if (!$petugas || !db_val('SELECT 1 FROM users WHERE id = ?', [$petugas])) {
        $petugas = fx_petugas();
    }
    $id = simpan_register(data_register(), null);
    verifikasi_anggota($id, $petugas, $tgl_verifikasi);
    foreach ($o as $kolom => $nilai) {
        db()->prepare("UPDATE anggota SET $kolom = ? WHERE id = ?")->execute([$nilai, $id]);
    }
    return $id;
}

function user_id_anggota(int $anggota_id): int
{
    return (int) db_val('SELECT user_id FROM anggota WHERE id = ?', [$anggota_id]);
}

function detail_id_dari(int $peminjaman_id): int
{
    return (int) db_val('SELECT MIN(id) FROM peminjaman_detail WHERE peminjaman_id = ?', [$peminjaman_id]);
}
