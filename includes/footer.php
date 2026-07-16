<?php
// Load settings if not already loaded
if (!isset($cfg) || empty($cfg)) {
    try {
        $footerRows = fetchAll("SELECT `key`, `value` FROM settings");
        $cfg = array_column($footerRows, 'value', 'key');
    } catch(Exception $e) { $cfg = []; }
}
$footerTagline = htmlspecialchars($cfg['footer_tagline'] ?? 'Helping businesses run smarter with technology, automation, and digital systems.');
$socialTwitter  = htmlspecialchars($cfg['social_twitter']   ?? '#');
$socialLinkedin = htmlspecialchars($cfg['social_linkedin']  ?? '#');
$socialInsta    = htmlspecialchars($cfg['social_instagram'] ?? '#');
$socialFacebook = htmlspecialchars($cfg['social_facebook']  ?? '#');
?>

<!-- ===== FOOTER (minimal) ===== -->
<footer class="tm2-footer">
    <div class="tm2-container">
        <div class="tm2-footer-top">
            <a href="<?= SITE_URL ?>/" class="tm2-logo">
                <img src="<?= SITE_URL ?>/assets/images/tedmark-logo-nav.png" alt="Tedmark Digital Agency">
            </a>
            <div class="tm2-footer-links">
                <a href="<?= SITE_URL ?>/about">About</a>
                <a href="<?= SITE_URL ?>/services">Services</a>
                <a href="<?= SITE_URL ?>/portfolio">Portfolio</a>
                <a href="<?= SITE_URL ?>/blog">Blog</a>
                <a href="<?= SITE_URL ?>/contact">Contact</a>
            </div>
            <div class="tm2-social-row">
                <a href="<?= $socialFacebook ?>" class="tm2-social"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="<?= $socialLinkedin ?>" class="tm2-social"><i class="fa-brands fa-linkedin-in"></i></a>
                <a href="<?= $socialTwitter ?>" class="tm2-social"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="<?= $socialInsta ?>" class="tm2-social"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
        <div class="tm2-footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($cfg['site_name'] ?? 'Tedmark Digital Agency') ?>. All rights reserved.</p>
            <div class="tm2-footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top -->
<button id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="tm-btt">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/app-v2.js?v=<?= filemtime(__DIR__.'/../assets/js/app-v2.js') ?>"></script>
</body>
</html>
