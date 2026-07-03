<!DOCTYPE html>
<html lang="en-GH">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__.'/../assets/css/style.css') ?>">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="tm-navbar<?= !($pageHasDarkHero ?? false) ? ' scrolled' : '' ?>" id="navbar" data-dark-hero="<?= ($pageHasDarkHero ?? false) ? '1' : '0' ?>">
    <div class="tm-container">
        <div class="tm-nav-inner">

            <!-- Logo -->
            <a href="<?= SITE_URL ?>/" class="tm-logo" style="display:flex;align-items:center;">
                <img src="<?= SITE_URL ?>/assets/images/tedmark logo copy2.png" alt="Tedmark Digital Agency" style="height:140px;width:auto;display:block;">
            </a>

            <!-- Desktop links -->
            <div class="tm-nav-links" id="nav-links">
                <a href="<?= SITE_URL ?>/" class="tm-nav-link <?= getActivePage('index') ?>">Home</a>
                <a href="<?= SITE_URL ?>/about.php" class="tm-nav-link <?= getActivePage('about') ?>">About</a>
                <div class="tm-dropdown" id="services-drop">
                    <button class="tm-nav-link tm-drop-btn" onclick="toggleDrop()">
                        Services <i class="fa-solid fa-chevron-down fa-2xs tm-drop-arrow"></i>
                    </button>
                    <div class="tm-drop-menu" id="drop-menu">
                        <a href="<?= SITE_URL ?>/services.php" class="tm-drop-item">
                            <div class="tm-drop-icon" style="background:rgba(22,163,74,0.15);color:#4ade80;"><i class="fa-solid fa-gears"></i></div>
                            <div><div class="tm-drop-title">All Services</div><div class="tm-drop-desc">View everything we offer</div></div>
                        </a>
                        <a href="<?= SITE_URL ?>/solutions.php" class="tm-drop-item">
                            <div class="tm-drop-icon" style="background:rgba(139,92,246,0.15);color:#a78bfa;"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                            <div><div class="tm-drop-title">Solutions</div><div class="tm-drop-desc">Tailored business packages</div></div>
                        </a>
                        <a href="<?= SITE_URL ?>/industries.php" class="tm-drop-item">
                            <div class="tm-drop-icon" style="background:rgba(59,130,246,0.15);color:#60a5fa;"><i class="fa-solid fa-building"></i></div>
                            <div><div class="tm-drop-title">Industries</div><div class="tm-drop-desc">Sectors we serve</div></div>
                        </a>
                    </div>
                </div>
                <a href="<?= SITE_URL ?>/portfolio.php" class="tm-nav-link <?= getActivePage('portfolio') ?>">Portfolio</a>
                <a href="<?= SITE_URL ?>/blog.php" class="tm-nav-link <?= getActivePage('blog') ?>">Blog</a>
                <a href="<?= SITE_URL ?>/resources.php" class="tm-nav-link <?= getActivePage('resources') ?>">Resources</a>
                <a href="<?= SITE_URL ?>/contact.php" class="tm-nav-link <?= getActivePage('contact') ?>">Contact</a>
            </div>

            <!-- CTA -->
            <a href="<?= SITE_URL ?>/consultation.php" class="tm-btn-nav">
                Book a Consultation <i class="fa-solid fa-arrow-right fa-xs"></i>
            </a>

            <!-- Mobile toggle -->
            <button class="tm-hamburger" id="hamburger" onclick="toggleMobile()">
                <i class="fa-solid fa-bars" id="ham-icon"></i>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="tm-mobile-menu" id="mobile-menu">
        <a href="<?= SITE_URL ?>/" class="tm-mobile-link">Home</a>
        <a href="<?= SITE_URL ?>/about.php" class="tm-mobile-link">About</a>
        <a href="<?= SITE_URL ?>/services.php" class="tm-mobile-link">Services</a>
        <a href="<?= SITE_URL ?>/solutions.php" class="tm-mobile-link">Solutions</a>
        <a href="<?= SITE_URL ?>/industries.php" class="tm-mobile-link">Industries</a>
        <a href="<?= SITE_URL ?>/portfolio.php" class="tm-mobile-link">Portfolio</a>
        <a href="<?= SITE_URL ?>/blog.php" class="tm-mobile-link">Blog</a>
        <a href="<?= SITE_URL ?>/resources.php" class="tm-mobile-link">Resources</a>
        <a href="<?= SITE_URL ?>/contact.php" class="tm-mobile-link">Contact</a>
        <div style="padding:12px 16px;">
            <a href="<?= SITE_URL ?>/consultation.php" class="tm-btn-primary" style="display:block;text-align:center;">Book a Consultation</a>
        </div>
    </div>
</nav>
