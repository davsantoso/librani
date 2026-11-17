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

