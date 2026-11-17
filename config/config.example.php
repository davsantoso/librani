<?php
// Salin file ini menjadi config.php lalu sesuaikan isinya.
// config.php tidak ikut di-commit (lihat .gitignore).

define('APP_ENV', 'lokal');                 // lokal | hosting
define('BASE_URL', 'http://localhost/librani');  // tanpa garis miring di akhir

define('DB_HOST', '127.0.0.1');             // InfinityFree: pakai host MySQL dari panel
define('DB_NAME', getenv('LIBRANI_DB') ?: 'librani');
define('DB_USER', 'root');
define('DB_PASS', '');
