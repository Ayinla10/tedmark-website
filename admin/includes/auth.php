<?php
require_once __DIR__ . '/../../includes/config.php';

function requireLogin() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function currentAdmin() {
    return $_SESSION['admin'] ?? null;
}

function isLoggedIn() {
    return !empty($_SESSION['admin_id']);
}
