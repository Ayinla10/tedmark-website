<?php
require_once __DIR__ . '/../includes/config.php';

echo "<pre style='background:#1e293b;color:#e2e8f0;padding:20px;font-size:13px;'>";
echo "SITE_URL: " . SITE_URL . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'not set') . "\n";
echo "REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'not set') . "\n";
echo "SESSION ID: " . session_id() . "\n";
echo "SESSION DATA: "; print_r($_SESSION);
echo "\nadmin_id in session: " . (empty($_SESSION['admin_id']) ? 'NOT SET' : $_SESSION['admin_id']) . "\n";
echo "</pre>";
