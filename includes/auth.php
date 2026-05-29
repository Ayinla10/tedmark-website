<?php
require_once __DIR__ . '/db.php';

function isAdminLoggedIn(): bool {
    if (!isset($_SESSION['admin_id'], $_SESSION['admin_login_time'])) {
        return false;
    }
    if (time() - $_SESSION['admin_login_time'] > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    $_SESSION['admin_login_time'] = time();
    return true;
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function adminLogin(string $email, string $password): bool {
    $admin = fetchOne("SELECT * FROM admins WHERE email = ?", [$email]);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_login_time'] = time();
        query("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);
        return true;
    }
    return false;
}

function adminLogout(): void {
    session_unset();
    session_destroy();
}

function currentAdmin(): array {
    return [
        'id' => $_SESSION['admin_id'] ?? 0,
        'name' => $_SESSION['admin_name'] ?? '',
        'email' => $_SESSION['admin_email'] ?? '',
        'role' => $_SESSION['admin_role'] ?? '',
    ];
}

function generateCsrf(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrf() . '">';
}
