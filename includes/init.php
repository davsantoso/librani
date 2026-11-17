<?php
// Dimuat oleh setiap halaman: config, koneksi DB, helper, CSRF, auth, session.
require_once __DIR__ . '/../config/config.php';

date_default_timezone_set('Asia/Jakarta');
error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'lokal' ? '1' : '0');

define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . '/uploads');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/nomor.php';
require_once __DIR__ . '/master.php';
require_once __DIR__ . '/katalog.php';
require_once __DIR__ . '/sirkulasi.php';
require_once __DIR__ . '/reservasi.php';
require_once __DIR__ . '/dashboard.php';
require_once __DIR__ . '/laporan.php';

// Di hosting: error tidak ditampilkan (display_errors mati), jadi tampilkan halaman ramah dan catat detailnya.
if (APP_ENV !== 'lokal') {
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_DIR . '/config/error.log');   // folder config/ ditolak dari web (.htaccess)
    $halaman_error = function (): void {
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Terjadi kesalahan · Librani</title><link rel="stylesheet" href="' . BASE_URL . '/assets/vendor/bootstrap.min.css"></head>'
            . '<body class="bg-light"><div class="container text-center py-5"><h1 class="h3">Terjadi kesalahan</h1>'
            . '<p class="text-muted">Permintaan tidak dapat diproses. Silakan coba lagi; jika berulang, hubungi admin.</p>'
            . '<a class="btn btn-primary" href="' . BASE_URL . '/">Kembali ke beranda</a></div></body></html>';
    };
    set_exception_handler(function (Throwable $e) use ($halaman_error): void {
        error_log('Uncaught ' . $e);
        $halaman_error();
    });
    // Fatal error (mis. memori habis) bukan exception; PHP sudah mencatatnya ke log.
    register_shutdown_function(function () use ($halaman_error): void {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $halaman_error();
        }
    });
}

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}
