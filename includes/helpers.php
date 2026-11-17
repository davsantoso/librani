<?php
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/** flash('sukses', 'Tersimpan') menyimpan; flash() mengambil lalu menghapus. */
function flash(?string $tipe = null, ?string $pesan = null): ?array
{
    if ($tipe !== null) {
        $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function rupiah(int|float $n): string
{
    return 'Rp' . number_format($n, 0, ',', '.');
}

function tgl(?string $ymd): string
{
    if (!$ymd) {
        return '-';
    }
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($ymd);
    return date('j', $t) . ' ' . $bulan[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
}

function hari_ini(): string
{
    return date('Y-m-d');
}

function tambah_hari(string $ymd, int $hari): string
{
    return date('Y-m-d', strtotime("$ymd +$hari day"));
}

/** Nilai pengaturan; di-cache per request (dikosongkan oleh simpan_setting/reset_db). */
function setting(string $kunci): string
{
    $GLOBALS['_setting'] ??= array_column(db_all('SELECT kunci, nilai FROM pengaturan'), 'nilai', 'kunci');
    return $GLOBALS['_setting'][$kunci] ?? '';
}

function post(string $k, string $default = ''): string
{
    return is_string($_POST[$k] ?? null) ? trim($_POST[$k]) : $default;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

/** Sampul buku: <img> lazy jika ada foto, selain itu placeholder berisi judul. $ukuran 'thumb' | 'besar'. */
function cover_html(array $buku, string $ukuran = 'thumb'): string
{
    $file = $ukuran === 'thumb' ? ($buku['foto_thumb'] ?? null) : ($buku['foto_cover'] ?? null);
    $path = $ukuran === 'thumb' ? 'uploads/cover/thumb/' : 'uploads/cover/';
    if ($file) {
        return '<img class="cover" loading="lazy" src="' . e(url($path . $file)) . '" alt="Sampul ' . e($buku['judul']) . '">';
    }
    return '<div class="cover-placeholder" role="img" aria-label="Sampul ' . e($buku['judul']) . '"><span>' . e($buku['judul']) . '</span></div>';
}
