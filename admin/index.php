<?php
require_once __DIR__ . '/includes/auth.php';
header('Location: ' . (isLoggedIn() ? '../admin/dashboard.php' : '../admin/login.php'));
exit;
