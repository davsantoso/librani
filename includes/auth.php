<?php
/** Cek username/email + password. Mengembalikan baris user aktif atau null. */
function coba_login(string $login, string $password): ?array
{
    $u = db_row('SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1', [$login, $login]);
    return $u && password_verify($password, $u['password_hash']) ? $u : null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
}

/** User yang sedang login (dibaca ulang dari DB agar nonaktif langsung berlaku). */
function user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cache = [];
    return $cache[$_SESSION['user_id']] ??= db_row(
        'SELECT id, nama, username, email, role FROM users WHERE id = ? AND is_active = 1',
        [$_SESSION['user_id']]
    );
}

function require_login(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate');
    if (!user()) {
        redirect('modules/auth/login.php');
    }
}

function require_role(string ...$roles): void
{
    require_login();
    if (!in_array(user()['role'], $roles, true)) {
        http_response_code(403);
        require ROOT_DIR . '/modules/auth/403.php';
        exit;
    }
}

function anggota_saya(): ?array
{
    $u = user();
    return $u ? db_row('SELECT * FROM anggota WHERE user_id = ?', [$u['id']]) : null;
}

function dashboard_url(string $role): string
{
    return url('modules/dashboard/index.php');
}
