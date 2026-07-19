<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/audit-ai.php';

if (empty($_SESSION['audit_unlocked']) || empty($_SESSION['audit_results'])) {
    header('Location: ' . SITE_URL . '/tools/website-audit');
    exit;
}

$results = $_SESSION['audit_results'];
$checks  = $results['checks'];
$domain  = parse_url($results['url'], PHP_URL_HOST);

$pageTitle = "Audit Report: $domain";
$pageDesc  = 'Your full website audit report.';

$aiSummary = auditGenerateSummary(
    $results['url'], $checks,
    $results['page_title'] ?? '', $results['meta_desc'] ?? '', $results['word_count'] ?? 0
);

require_once __DIR__ . '/../includes/header.php';

function auditStatusIcon($status) {
    return match($status) {
        'pass' => '<i class="fa-solid fa-circle-check" style="color:#16a34a;"></i>',
        'warn' => '<i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i>',
        default => '<i class="fa-solid fa-circle-xmark" style="color:#dc2626;"></i>',
    };
}

$categories = [];
foreach ($checks as $c) { $categories[$c['category']][] = $c; }
?>

<section class="audit-page" style="padding:48px 0 96px;background:#f8fafc;">
    <div class="tm-container" style="max-width:760px;">
        <div class="audit-no-print" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:0.72rem;font-weight:500;color:#16a34a;text-transform:uppercase;letter-spacing:.06em;">Full Audit Report</div>
                <h1 style="font-size:1.4rem;font-weight:600;color:#0f172a;margin-top:4px;"><?= htmlspecialchars($domain) ?></h1>
            </div>
            <button onclick="window.print()" class="tm-btn-primary" style="font-weight:500;"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
        </div>

        <?php if ($aiSummary): ?>
        <div style="background:#0f172a;border-radius:20px;padding:32px;color:#fff;margin-bottom:28px;">
            <div style="font-size:0.72rem;font-weight:500;color:#4ade80;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;"><i class="fa-solid fa-sparkles"></i> AI Executive Summary</div>
            <div style="color:#e2e8f0;font-size:0.9rem;font-weight:300;line-height:1.7;white-space:pre-line;"><?= htmlspecialchars($aiSummary) ?></div>
        </div>
        <?php endif; ?>

        <div class="audit-print-area" style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.06);padding:36px;">
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;margin-bottom:24px;">
                <span style="color:#166534;font-size:0.85rem;font-weight:500;"><i class="fa-solid fa-circle-check"></i> Score: <?= $results['score'] ?>/100 &middot; <?= count($checks) ?> checks across <?= $results['pages_scanned'] ?> page<?= $results['pages_scanned']==1?'':'s' ?></span>
            </div>
            <?php foreach ($categories as $catName => $catChecks): ?>
            <h3 style="margin:28px 0 14px;text-transform:uppercase;letter-spacing:.04em;font-size:0.8rem;color:#16a34a;font-weight:600;"><?= htmlspecialchars($catName) ?></h3>
            <?php foreach ($catChecks as $c): ?>
            <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                <span style="flex-shrink:0;margin-top:2px;"><?= auditStatusIcon($c['status']) ?></span>
                <div>
                    <div style="font-weight:500;color:#0f172a;font-size:0.9rem;"><?= htmlspecialchars($c['label']) ?></div>
                    <div style="color:#64748b;font-size:0.85rem;font-weight:300;"><?= htmlspecialchars($c['detail']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <div class="audit-no-print" style="text-align:center;margin-top:28px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>/consultation" class="tm-btn-green" style="font-weight:500;">Get Help Fixing These <i class="fa-solid fa-arrow-right fa-xs"></i></a>
            <a href="<?= SITE_URL ?>/tools/website-audit?reset=1" class="tm-btn-outline">Scan Another Site</a>
        </div>
    </div>
</section>

<style>
.audit-page, .audit-page * { font-family: 'Geist', sans-serif; }
@media print {
    header, footer, .tm2-nav-wrap, .tm2-announce-bar, .audit-no-print { display: none !important; }
    body { background: #fff !important; }
    .audit-print-area { box-shadow: none !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
