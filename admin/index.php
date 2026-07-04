<?php
require_once __DIR__ . '/includes/auth.php';
header('Location: ' . (isLoggedIn() ? SITE_URL . '/admin/dashboard.php' : SITE_URL . '/admin/login.php'));
exit;
