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
<section style="background:#f8fafc;padding:48px 0;">
    <div class="tm-container">
        <div style="background:#166534;border-radius:20px;padding:36px 40px;display:flex;align-items:center;gap:32px;flex-wrap:wrap;position:relative;overflow:hidden;background-image:linear-gradient(135deg,#166534 0%,#14532d 100%);">
            <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.05) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;border-radius:20px;"></div>
            <div style="position:absolute;top:-40%;left:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(74,222,128,0.15) 0%,transparent 65%);pointer-events:none;"></div>
            <div style="width:60px;height:60px;border-radius:14px;background:rgba(74,222,128,0.15);border:1px solid rgba(74,222,128,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;z-index:1;">
                <i class="fa-solid fa-rocket" style="font-size:1.5rem;color:#4ade80;"></i>
            </div>
            <div style="flex:1;min-width:220px;position:relative;z-index:1;">
                <h2 style="font-size:1.3rem;font-weight:900;color:#fff;margin:0 0 6px;line-height:1.3;"><?= $ctaHeading ?></h2>
                <p style="font-size:0.85rem;color:#86efac;margin:0;line-height:1.65;"><?= $ctaSubtext ?></p>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;flex-shrink:0;position:relative;z-index:1;">
                <a href="<?= SITE_URL ?>/consultation.php" style="display:inline-flex;align-items:center;gap:8px;background:#f59e0b;color:#0f172a;padding:12px 22px;border-radius:8px;font-weight:800;font-size:14px;text-decoration:none;white-space:nowrap;">
                    <?= $ctaBtn1 ?> <i class="fa-solid fa-arrow-right fa-xs"></i>
                </a>
                <a href="<?= SITE_URL ?>/contact.php" style="display:inline-flex;align-items:center;gap:8px;background:transparent;color:#fff;padding:12px 22px;border-radius:8px;font-weight:700;font-size:14px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.3);white-space:nowrap;">
                    <?= $ctaBtn2 ?>
                </a>
            </div>
        </div>
    </div>
</section>
