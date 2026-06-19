<?php
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $s = db()->prepare('SELECT * FROM users WHERE id=:id LIMIT 1');
        $s->execute(['id' => $_SESSION['user_id']]);
        $user = $s->fetch() ?: null;
    }
    return $user;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

function require_admin(): void {
    if (!is_logged_in()) {
        redirect('/gym-system/login.php?auth=unauthenticated');
    }
    if (!is_admin()) {
        redirect('/gym-system/login.php?auth=unauthorized');
    }
}

function require_user(): void {
    if (!is_logged_in()) {
        redirect('/gym-system/login.php?auth=unauthenticated');
    }
    if (is_admin()) {
        redirect('/gym-system/views/admin/dashboard.php');
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['created_at'] = time();
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function reason_message(): string {
    $map = [
        'unauthenticated' => 'Please login to continue.',
        'unauthorized'    => 'You do not have permission to access that page.',
        'blocked'         => 'Your account has been suspended. Contact admin.',
        'session_expired' => 'Your session expired. Please login again.',
        'logged_out'      => 'You have been logged out.',
    ];
    $r = get('auth');
    return $r ? ($map[$r] ?? '') : '';
}