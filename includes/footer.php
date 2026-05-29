
<!-- ===== FOOTER ===== -->
<footer style="background:#020917;padding:56px 0 0;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:40px;align-items:start;">

            <!-- Brand -->
            <div>
                <img src="<?= SITE_URL ?>/assets/images/tedmark logo copy2.png" alt="Tedmark Digital Agency" style="height:180px;width:auto;display:block;margin-top:-60px;margin-bottom:-10px;margin-left:-8px;">
                <p style="color:#cbd5e1;font-size:13px;line-height:1.75;max-width:240px;margin-bottom:18px;">
                    Helping African businesses run smarter with technology, automation, and digital systems.
                </p>
                <div class="tm-social-row">
                    <a href="#" class="tm-social"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="tm-social"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" class="tm-social"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" class="tm-social"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="tm-footer-heading">Quick Links</h4>
                <ul class="tm-footer-list">
                    <li><a href="<?= SITE_URL ?>/about.php">About</a></li>
                    <li><a href="<?= SITE_URL ?>/services.php">Services</a></li>
                    <li><a href="<?= SITE_URL ?>/portfolio.php">Portfolio</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div>
                <h4 class="tm-footer-heading">Our Services</h4>
                <ul class="tm-footer-list">
                    <li><a href="<?= SITE_URL ?>/services.php">Web Development</a></li>
                    <li><a href="<?= SITE_URL ?>/services.php">Business Systems</a></li>
                    <li><a href="<?= SITE_URL ?>/services.php">IT Consulting</a></li>
                    <li><a href="<?= SITE_URL ?>/services.php">E-Commerce</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h4 class="tm-footer-heading">Resources</h4>
                <ul class="tm-footer-list">
                    <li><a href="<?= SITE_URL ?>/contact.php#faq">FAQs</a></li>
                    <li><a href="<?= SITE_URL ?>/resources.php">Free Resources</a></li>
                    <li><a href="<?= SITE_URL ?>/resources.php">Templates</a></li>
                </ul>
            </div>

        </div>
    </div>

    <!-- Bottom bar -->
    <div style="border-top:1px solid #0f172a;margin-top:40px;padding:18px 0;">
        <div class="tm-container">
            <div class="tm-footer-bottom">
                <p style="color:#94a3b8;font-size:12px;">&copy; <?= date('Y') ?> Tedmark Digital Agency. All rights reserved.</p>
                <div class="tm-footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top -->
<button id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="tm-btt">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js?v=<?= filemtime(__DIR__.'/../assets/js/main.js') ?>"></script>
</body>
</html>
