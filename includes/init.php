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

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}
