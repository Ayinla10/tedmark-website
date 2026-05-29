<?php
// Tedmark Digital Agency - Configuration

// Auto-detect environment
$_isLive = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;

if ($_isLive) {
    // ── LIVE (Namecheap) ──────────────────────────────
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'hopewwkz_tedmark');
    define('DB_USER', 'hopewwkz_tedmark');
    define('DB_PASS', 'D[J;v{1qsaQQWS1N');
    define('SITE_URL', 'https://new.tedmarkdigital.com');
} else {
    // ── LOCAL ─────────────────────────────────────────
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'tedmark_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('SITE_URL', 'http://localhost:8080');
}

define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME', 'Tedmark Digital Agency');
define('SITE_EMAIL', 'hello@tedmarkdigital.com');
define('ADMIN_EMAIL', 'admin@tedmarkdigital.com');

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_NAME', '_tedmark_csrf');

date_default_timezone_set('Africa/Lagos');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'secure' => $_isLive,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
