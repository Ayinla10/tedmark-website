<?php
// Load settings if not already loaded
if (!isset($cfg) || empty($cfg)) {
    try {
        $ctaRows = fetchAll("SELECT `key`, `value` FROM settings");
        $cfg = array_column($ctaRows, 'value', 'key');
    } catch(Exception $e) { $cfg = []; }
}
$ctaHeading = htmlspecialchars($cfg['cta_heading'] ?? 'Ready to Run Your Business Smarter?');
$ctaSubtext = htmlspecialchars($cfg['cta_subtext'] ?? "Let's build systems that save you time, reduce costs, and help you grow without limits.");
$ctaBtn1    = htmlspecialchars($cfg['cta_btn_primary'] ?? 'Book a Free Strategy Session');
$ctaBtn2    = htmlspecialchars($cfg['cta_btn_secondary'] ?? 'Talk to an Expert');
?>
<!-- ===== CTA BAND ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-cta-band">
            <div class="tm2-card-icon" style="margin:0 auto 20px;width:52px;height:52px;">
                <i class="fa-solid fa-rocket"></i>
            </div>
            <h2 class="tm2-h2" style="margin-bottom:10px;"><?= $ctaHeading ?></h2>
            <p class="tm2-sub" style="max-width:480px;margin-left:auto;margin-right:auto;margin-bottom:28px;"><?= $ctaSubtext ?></p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
                <a href="<?= SITE_URL ?>/consultation" class="tm2-btn tm2-btn-primary">
                    <?= $ctaBtn1 ?> <i class="fa-solid fa-arrow-right fa-xs"></i>
                </a>
                <a href="<?= SITE_URL ?>/contact" class="tm2-btn tm2-btn-outline">
                    <?= $ctaBtn2 ?>
                </a>
            </div>
        </div>
    </div>
</section>
