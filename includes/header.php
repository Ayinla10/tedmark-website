<!DOCTYPE html>
<html lang="en-GH">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var t=localStorage.getItem('tm_theme')||'dark';document.documentElement.setAttribute('data-theme',t);})();</script>
<?php
// ── SEO Engine ────────────────────────────────────────────────
if (!function_exists('renderSeoTags')) {
    require_once __DIR__ . '/seo.php';
}
// Load $cfg if not already loaded
if (empty($cfg)) {
    try {
        $rows = fetchAll("SELECT `key`, `value` FROM settings");
        $cfg  = array_column($rows, 'value', 'key');
    } catch(Exception $e) { $cfg = []; }
}
// $seoData can be set on any page before including header.php
// Fallback to $pageTitle / $pageDesc if no $seoData
if (empty($seoData)) {
    $seoData = [];
    if (!empty($pageTitle))   $seoData['title']       = $pageTitle;
    if (!empty($pageDesc))    $seoData['description']  = $pageDesc;
    if (!empty($pageSeoPage)) $seoData['page']         = $pageSeoPage;
}
renderSeoTags($cfg, $seoData);
?>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= SITE_URL ?>/assets/images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= SITE_URL ?>/assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= SITE_URL ?>/assets/images/favicon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= SITE_URL ?>/assets/images/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__.'/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/mobile.css?v=<?= filemtime(__DIR__.'/../assets/css/mobile.css') ?>">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/theme-v2.css?v=<?= filemtime(__DIR__.'/../assets/css/theme-v2.css') ?>">
</head>
<body>

<!-- ===== ANNOUNCEMENT BAR ===== -->
<a href="<?= SITE_URL ?>/tools/business-health.php" class="tm2-announce-bar">
    <span class="tm2-announce-dot"></span>
    Wondering if your business is AI-ready? <span class="tm2-announce-strong">Take our free 3-minute Business Health Scan</span>
    <i class="fa-solid fa-arrow-right fa-xs"></i>
</a>

<!-- ===== NAVBAR (pill, theme-aware) ===== -->
<div class="tm2-nav-wrap">
  <div class="tm2-nav" style="max-width:1100px;">
    <a href="<?= SITE_URL ?>/" class="tm2-logo">
        <img src="<?= SITE_URL ?>/assets/images/tedmark-logo-nav.png" alt="Tedmark Digital Agency">
    </a>

    <div class="tm2-links">
        <a href="<?= SITE_URL ?>/" class="<?= getActivePage('index') ?>">Home</a>
        <a href="<?= SITE_URL ?>/about.php" class="<?= getActivePage('about') ?>">About</a>
        <a href="<?= SITE_URL ?>/services.php" class="<?= getActivePage('services') ?>">Services</a>
        <a href="<?= SITE_URL ?>/industries.php" class="<?= getActivePage('industries') ?>">Industries</a>
        <a href="<?= SITE_URL ?>/portfolio.php" class="<?= getActivePage('portfolio') ?>">Portfolio</a>
        <a href="<?= SITE_URL ?>/blog.php" class="<?= getActivePage('blog') ?>">Blog</a>
        <a href="<?= SITE_URL ?>/contact.php" class="<?= getActivePage('contact') ?>">Contact</a>
    </div>

    <div class="tm2-nav-right">
        <button class="tm2-toggle" onclick="toggleTheme()" title="Toggle theme">
            <i class="tm2-toggle-icon fa-solid fa-moon"></i>
        </button>
        <a href="<?= SITE_URL ?>/consultation.php" class="tm2-cta">Book a Call</a>
        <button class="tm2-burger" onclick="toggleMobile2()"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>

  <div class="tm2-mobile-menu" id="tm2-mobile-menu" style="max-width:1100px;width:100%;">
    <a href="<?= SITE_URL ?>/">Home</a>
    <a href="<?= SITE_URL ?>/about.php">About</a>
    <a href="<?= SITE_URL ?>/services.php">Services</a>
    <a href="<?= SITE_URL ?>/industries.php">Industries</a>
    <a href="<?= SITE_URL ?>/portfolio.php">Portfolio</a>
    <a href="<?= SITE_URL ?>/blog.php">Blog</a>
    <a href="<?= SITE_URL ?>/resources.php">Resources</a>
    <a href="<?= SITE_URL ?>/contact.php">Contact</a>
    <a href="<?= SITE_URL ?>/consultation.php" class="tm2-btn tm2-btn-primary" style="justify-content:center;margin-top:6px;">Book a Consultation</a>
  </div>
</div>
