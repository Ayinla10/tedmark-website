<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/audit-engine.php';

try {
    $settingsRows = fetchAll("SELECT `key`, `value` FROM settings");
    $cfg = array_column($settingsRows, 'value', 'key');
} catch(Exception $e) { $cfg = []; }
function toolcfg($cfg, $key, $default='') { return htmlspecialchars($cfg[$key] ?? $default); }

$pageTitle = 'Free Website Audit';
$pageDesc  = 'Run a free, live technical, SEO, and security audit of your website in seconds.';
$pageHasDarkHero = true;

$error = '';
$results = null;
$scannedUrl = '';

// ── Handle "start scan" ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_url'])) {
    $inputUrl = trim($_POST['scan_url'] ?? '');
    if ($inputUrl === '') {
        $error = 'Please enter a website URL.';
    } else {
        $audit = runWebsiteAudit($inputUrl);
        if (!$audit['ok']) {
            $error = $audit['error'];
        } else {
            $_SESSION['audit_results']  = $audit;
            $_SESSION['audit_unlocked'] = false;
            $results    = $audit;
            $scannedUrl = $audit['url'];
        }
    }
} elseif (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_email'])) || !empty($_SESSION['audit_results'])) {
    $results    = $_SESSION['audit_results'] ?? null;
    $scannedUrl = $results['url'] ?? '';
}

// ── Handle "unlock full report" ──────────────────────────
$justUnlocked = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_email']) && $results) {
    $email = trim($_POST['unlock_email'] ?? '');
    $name  = trim($_POST['unlock_name'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid business email address.';
    } else {
        try {
            insert('website_audits', [
                'target_url' => $scannedUrl,
                'email'      => $email,
                'name'       => $name,
                'score'      => $results['score'],
                'results'    => json_encode($results),
                'unlocked'   => 1,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Exception $e) {}
        $_SESSION['audit_unlocked'] = true;
        $justUnlocked = true;
    }
}

$unlocked = !empty($_SESSION['audit_unlocked']);

// New scan clears state
if (isset($_GET['reset'])) {
    unset($_SESSION['audit_results'], $_SESSION['audit_unlocked']);
    header('Location: ' . SITE_URL . '/tools/website-audit');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

function auditStatusIcon($status) {
    return match($status) {
        'pass' => '<i class="fa-solid fa-circle-check" style="color:#16a34a;"></i>',
        'warn' => '<i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i>',
        default => '<i class="fa-solid fa-circle-xmark" style="color:#dc2626;"></i>',
    };
}
?>

<!-- ===== HERO ===== -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container">
        <div class="tm-page-hero-inner" style="text-align:center;max-width:640px;margin:0 auto;">
            <div class="tm-badge tm-fade" style="animation-delay:.05s">
                <i class="fa-solid fa-magnifying-glass-chart"></i> Free AI Website Audit
            </div>
            <h1 class="tm-page-hero-title tm-fade" style="animation-delay:.1s"><?= toolcfg($cfg,'tool_audit_h1','Find Out What\'s Costing You Customers') ?></h1>
            <p class="tm-page-hero-desc tm-fade" style="animation-delay:.15s">
                <?= toolcfg($cfg,'tool_audit_subtext','A real, live scan of your site\'s SEO, performance, and security, in about 10 seconds.') ?>
            </p>
        </div>
    </div>
</section>

<section style="padding:60px 0 96px;background:#f8fafc;">
    <div class="tm-container" style="max-width:760px;">

        <?php if (!$results): ?>
        <!-- ===== STEP 1: URL SCAN ===== -->
        <div style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.07);padding:40px;">
            <?php if ($error): ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:20px;">
                <span style="color:#dc2626;font-size:0.875rem;font-weight:600;"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            <form method="POST" id="audit-form">
                <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;text-transform:uppercase;letter-spacing:.04em;">Website URL</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <input type="text" name="scan_url" placeholder="https://www.company.com" required
                        style="flex:1;min-width:220px;padding:16px 18px;border:2px solid #e2e8f0;border-radius:12px;font-size:16px;font-weight:500;color:#0f172a;box-sizing:border-box;outline:none;">
                    <button type="submit" id="audit-submit" class="tm-btn-green" style="padding:16px 28px;font-size:1rem;white-space:nowrap;">
                        <span id="audit-submit-label">Start Free AI Audit</span> <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </button>
                </div>
                <p style="font-size:0.78rem;color:#94a3b8;margin-top:12px;">We only scan the public homepage. No login, no install, nothing to configure.</p>
            </form>
        </div>
        <script>
        document.getElementById('audit-form').addEventListener('submit', function(){
            document.getElementById('audit-submit-label').textContent = 'Scanning...';
            document.getElementById('audit-submit').disabled = true;
        });
        </script>

        <?php else: $score = $results['score']; $checks = $results['checks'];
            $scoreColor = $score >= 80 ? '#16a34a' : ($score >= 55 ? '#f59e0b' : '#dc2626');
            $failsAndWarns = array_values(array_filter($checks, fn($c) => $c['status'] !== 'pass'));
            usort($failsAndWarns, fn($a,$b) => $b['weight'] <=> $a['weight']);
            $teaser = array_slice($failsAndWarns, 0, 3);
            $categories = [];
            foreach ($checks as $c) { $categories[$c['category']][] = $c; }
        ?>

        <!-- ===== SCORE + TEASER ===== -->
        <div style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.07);padding:40px;margin-bottom:24px;text-align:center;">
            <p style="font-size:0.8rem;color:#94a3b8;word-break:break-all;margin-bottom:16px;"><?= htmlspecialchars($scannedUrl) ?></p>
            <div style="width:140px;height:140px;border-radius:50%;border:10px solid <?= $scoreColor ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <div>
                    <div style="font-size:2.4rem;font-weight:900;color:#0f172a;line-height:1;"><?= $score ?></div>
                    <div style="font-size:0.7rem;color:#94a3b8;">/ 100</div>
                </div>
            </div>
            <h2 style="font-size:1.3rem;font-weight:800;color:#0f172a;margin-bottom:6px;">
                <?= $score >= 80 ? 'Solid, but there\'s room to improve' : ($score >= 55 ? 'A few real issues are holding you back' : 'Your site has serious issues costing you traffic') ?>
            </h2>
            <p style="color:#64748b;font-size:0.9rem;">We ran <?= count($checks) ?> live checks across SEO, technical, security, and content.</p>
        </div>

        <?php if (!$unlocked): ?>
        <!-- ===== TEASER ISSUES ===== -->
        <div style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.07);padding:32px;margin-bottom:24px;">
            <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:16px;">Top issues we found:</h3>
            <?php foreach ($teaser as $c): ?>
            <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                <span style="flex-shrink:0;margin-top:2px;"><?= auditStatusIcon($c['status']) ?></span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:0.9rem;"><?= htmlspecialchars($c['label']) ?></div>
                    <div style="color:#64748b;font-size:0.85rem;"><?= htmlspecialchars($c['detail']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="position:relative;margin-top:20px;padding-top:20px;">
                <div style="filter:blur(4px);opacity:0.5;user-select:none;pointer-events:none;">
                    <?php foreach (array_slice($failsAndWarns, 3, 4) as $c): ?>
                    <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;">
                        <span style="flex-shrink:0;"><?= auditStatusIcon($c['status']) ?></span>
                        <div style="font-weight:700;color:#0f172a;font-size:0.9rem;"><?= htmlspecialchars($c['label']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(255,255,255,0) 0%,#fff 60%);">
                    <span style="font-size:0.85rem;font-weight:700;color:#0f172a;background:#f0fdf4;border:1px solid #bbf7d0;padding:8px 16px;border-radius:99px;">
                        +<?= max(0, count($checks) - 3) ?> more checks in the full report
                    </span>
                </div>
            </div>
        </div>

        <!-- ===== EMAIL GATE ===== -->
        <div style="background:#0f172a;border-radius:20px;padding:36px;color:#fff;">
            <h3 style="font-size:1.15rem;font-weight:800;margin-bottom:16px;">Your complete audit contains:</h3>
            <ul style="list-style:none;padding:0;margin:0 0 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
                <?php foreach(['All '.count($checks).' live technical checks','Full SEO analysis','Security header review','Complete issue list','Priority action plan','Printable / PDF report'] as $item): ?>
                <li style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:#e2e8f0;"><i class="fa-solid fa-check" style="color:#4ade80;"></i> <?= $item ?></li>
                <?php endforeach; ?>
            </ul>
            <p style="color:#94a3b8;font-size:0.85rem;margin-bottom:16px;">Enter your business email to unlock the full report.</p>
            <?php if ($error): ?>
            <div style="background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);border-radius:10px;padding:12px;margin-bottom:16px;">
                <span style="color:#fca5a5;font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;">
                <input type="email" name="unlock_email" placeholder="you@company.com" required
                    style="flex:1;min-width:200px;padding:14px 16px;border-radius:10px;border:1px solid #334155;background:#1e293b;color:#fff;font-size:0.95rem;outline:none;">
                <button type="submit" class="tm-btn-primary" style="padding:14px 24px;white-space:nowrap;">Download Full Audit Report</button>
            </form>
        </div>

        <?php else: ?>
        <!-- ===== FULL REPORT ===== -->
        <div class="audit-print-area" style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.07);padding:36px;">
            <?php if ($justUnlocked): ?>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;margin-bottom:24px;">
                <span style="color:#166534;font-size:0.85rem;font-weight:600;"><i class="fa-solid fa-circle-check"></i> Full report unlocked below.</span>
            </div>
            <?php endif; ?>
            <?php foreach ($categories as $catName => $catChecks): ?>
            <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin:28px 0 14px;text-transform:uppercase;letter-spacing:.04em;font-size:0.8rem;color:#16a34a;"><?= htmlspecialchars($catName) ?></h3>
            <?php foreach ($catChecks as $c): ?>
            <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                <span style="flex-shrink:0;margin-top:2px;"><?= auditStatusIcon($c['status']) ?></span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:0.9rem;"><?= htmlspecialchars($c['label']) ?></div>
                    <div style="color:#64748b;font-size:0.85rem;"><?= htmlspecialchars($c['detail']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <div class="audit-no-print" style="text-align:center;margin-top:28px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <button onclick="window.print()" class="tm-btn-primary"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
            <a href="<?= SITE_URL ?>/consultation" class="tm-btn-green">Get Help Fixing These <i class="fa-solid fa-arrow-right fa-xs"></i></a>
            <a href="<?= SITE_URL ?>/tools/website-audit?reset=1" class="tm-btn-outline">Scan Another Site</a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
@media print {
    header, footer, .tm-page-hero, .tm2-nav-wrap, .tm2-announce-bar, .audit-no-print { display: none !important; }
    body { background: #fff !important; }
    .audit-print-area { box-shadow: none !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
