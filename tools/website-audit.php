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

if (isset($_GET['reset'])) {
    unset($_SESSION['audit_results'], $_SESSION['audit_unlocked'], $_SESSION['audit_otp_pending'],
          $_SESSION['audit_otp_code'], $_SESSION['audit_otp_email'], $_SESSION['audit_otp_expires'], $_SESSION['audit_otp_attempts']);
    header('Location: ' . SITE_URL . '/tools/website-audit');
    exit;
}

// ── Step 1: run the scan ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_url'])) {
    $inputUrl = trim($_POST['scan_url'] ?? '');
    if ($inputUrl === '') {
        $error = 'Please enter a website URL.';
    } else {
        $audit = runWebsiteAudit($inputUrl);
        if (!$audit['ok']) {
            $error = $audit['error'];
        } else {
            unset($_SESSION['audit_unlocked'], $_SESSION['audit_otp_pending']);
            $_SESSION['audit_results'] = $audit;
        }
    }
}

// ── Step 2: request a verification code ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_code']) && !empty($_SESSION['audit_results'])) {
    $email = trim($_POST['unlock_email'] ?? '');
    $name  = trim($_POST['unlock_name'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid business email address.';
    } else {
        $code = (string) random_int(100000, 999999);
        $_SESSION['audit_otp_code']     = $code;
        $_SESSION['audit_otp_email']    = $email;
        $_SESSION['audit_otp_name']     = $name;
        $_SESSION['audit_otp_expires']  = time() + 600; // 10 minutes
        $_SESSION['audit_otp_attempts'] = 0;
        $_SESSION['audit_otp_pending']  = true;

        $sent = sendMail($email, "Your verification code: $code",
            "<p>Your Tedmark Digital website audit verification code is:</p>
             <p style='font-size:28px;font-weight:600;letter-spacing:4px;'>$code</p>
             <p>This code expires in 10 minutes. If you didn't request this, you can ignore this email.</p>");
        if (!$sent) {
            $error = "We couldn't send a verification email right now. Please try again in a moment.";
            unset($_SESSION['audit_otp_pending']);
        }
    }
}

// ── Step 3: verify the code ───────────────────────────────
$justUnlocked = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code']) && !empty($_SESSION['audit_otp_pending'])) {
    $entered = trim($_POST['otp_code'] ?? '');
    if (time() > ($_SESSION['audit_otp_expires'] ?? 0)) {
        $error = 'That code has expired. Please request a new one.';
    } elseif (($_SESSION['audit_otp_attempts'] ?? 0) >= 5) {
        $error = 'Too many incorrect attempts. Please request a new code.';
    } elseif (!hash_equals((string)$_SESSION['audit_otp_code'], $entered)) {
        $_SESSION['audit_otp_attempts'] = ($_SESSION['audit_otp_attempts'] ?? 0) + 1;
        $error = 'That code is incorrect. Please check your email and try again.';
    } else {
        $results = $_SESSION['audit_results'];
        try {
            insert('website_audits', [
                'target_url' => $results['url'],
                'email'      => $_SESSION['audit_otp_email'],
                'name'       => $_SESSION['audit_otp_name'] ?? '',
                'score'      => $results['score'],
                'results'    => json_encode($results),
                'unlocked'   => 1,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Exception $e) {}
        $_SESSION['audit_unlocked'] = true;
        $justUnlocked = true;
        unset($_SESSION['audit_otp_pending'], $_SESSION['audit_otp_code']);
    }
}

$results    = $_SESSION['audit_results'] ?? null;
$otpPending = !empty($_SESSION['audit_otp_pending']);
$unlocked   = !empty($_SESSION['audit_unlocked']);

require_once __DIR__ . '/../includes/header.php';

function auditBarColor($score) {
    return $score >= 80 ? '#16a34a' : ($score >= 55 ? '#f59e0b' : '#dc2626');
}
function auditSeverityBadge($c) {
    if ($c['status'] === 'fail') return ['CRITICAL', '#dc2626'];
    if ($c['status'] === 'warn' && $c['weight'] >= 2) return ['HIGH', '#f59e0b'];
    return ['MEDIUM', '#94a3b8'];
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

<section class="audit-page" style="padding:60px 0 96px;background:#f8fafc;">
    <div class="tm-container" style="max-width:900px;">

        <?php if (!$results): ?>
        <!-- ===== STEP 1: URL SCAN ===== -->
        <div style="background:#fff;border-radius:20px;box-shadow:0 4px 32px rgba(0,0,0,.06);padding:40px;max-width:640px;margin:0 auto;">
            <?php if ($error): ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:20px;">
                <span style="color:#dc2626;font-size:0.875rem;font-weight:500;"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            <form method="POST" id="audit-form">
                <label style="display:block;font-size:13px;font-weight:500;color:#64748b;margin-bottom:10px;text-transform:uppercase;letter-spacing:.04em;">Website URL</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <input type="text" name="scan_url" placeholder="https://www.company.com" required
                        style="flex:1;min-width:220px;padding:16px 18px;border:2px solid #e2e8f0;border-radius:12px;font-size:16px;font-weight:400;color:#0f172a;box-sizing:border-box;outline:none;">
                    <button type="submit" id="audit-submit" class="tm-btn-green" style="padding:16px 28px;font-size:1rem;font-weight:500;white-space:nowrap;">
                        <span id="audit-submit-label">Start Free AI Audit</span> <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </button>
                </div>
                <p style="font-size:0.78rem;color:#94a3b8;margin-top:12px;">We scan your homepage plus key linked pages (about, services, contact, and more), not just one page.</p>
            </form>
        </div>
        <script>
        document.getElementById('audit-form').addEventListener('submit', function(){
            document.getElementById('audit-submit-label').textContent = 'Scanning...';
            document.getElementById('audit-submit').disabled = true;
        });
        </script>

        <?php else:
            $score = $results['score'];
            $checks = $results['checks'];
            $catScores = $results['category_scores'];
            $domain = parse_url($results['url'], PHP_URL_HOST);
            $statusLabel = $score >= 80 ? 'Strong' : ($score >= 55 ? 'Developing' : 'At Risk');
            $failCount = count(array_filter($checks, fn($c) => $c['status'] === 'fail'));
            $warnCount = count(array_filter($checks, fn($c) => $c['status'] === 'warn'));
            $priority  = array_values(array_filter($checks, fn($c) => $c['status'] !== 'pass'));
            usort($priority, fn($a,$b) => $b['weight'] <=> $a['weight']);
        ?>

        <!-- ===== FORENSIC HERO CARD ===== -->
        <div style="background:#0f172a;border-radius:24px;padding:44px;color:#fff;display:grid;grid-template-columns:1.3fr 1fr;gap:32px;align-items:center;margin-bottom:32px;">
            <div>
                <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(220,38,38,0.15);color:#fca5a5;font-size:0.7rem;font-weight:500;letter-spacing:.06em;text-transform:uppercase;padding:5px 12px;border-radius:99px;margin-bottom:16px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Live Audit
                </div>
                <h2 style="font-size:1.8rem;font-weight:600;line-height:1.25;margin-bottom:14px;">A live look at<br><span style="color:#4ade80;"><?= htmlspecialchars($domain) ?></span></h2>
                <p style="color:#94a3b8;font-size:0.9rem;line-height:1.6;font-weight:300;margin-bottom:24px;">
                    <?= count($checks) ?> live checks across <?= $results['pages_scanned'] ?> page<?= $results['pages_scanned']==1?'':'s' ?>, covering SEO, technical, security, and content. <?= $failCount ?> critical issue<?= $failCount==1?'':'s' ?> found, <?= $warnCount ?> more worth fixing.
                </p>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="#full-report" class="tm-btn-primary" style="padding:12px 22px;font-size:0.9rem;font-weight:500;">View Findings <i class="fa-solid fa-arrow-down fa-xs"></i></a>
                    <a href="<?= SITE_URL ?>/tools/website-audit?reset=1" style="color:#94a3b8;font-size:0.85rem;font-weight:300;align-self:center;text-decoration:underline;">Scan a different site</a>
                </div>
            </div>
            <div style="background:#1e293b;border-radius:18px;padding:28px;text-align:center;">
                <div style="font-size:0.7rem;color:#94a3b8;font-weight:400;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Overall Score</div>
                <div style="font-size:3rem;font-weight:600;line-height:1;"><?= $score ?><span style="font-size:1.2rem;color:#64748b;font-weight:300;">/100</span></div>
                <div style="display:inline-block;margin-top:10px;background:rgba(74,222,128,0.12);color:#4ade80;font-size:0.72rem;font-weight:500;padding:4px 12px;border-radius:99px;"><?= $statusLabel ?></div>
                <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;text-align:left;">
                    <?php foreach ($catScores as $cat => $cs): ?>
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:#94a3b8;font-weight:300;margin-bottom:4px;"><span><?= htmlspecialchars($cat) ?></span><span><?= $cs ?>/100</span></div>
                        <div style="height:5px;background:#334155;border-radius:99px;overflow:hidden;"><div style="height:100%;width:<?= $cs ?>%;background:<?= auditBarColor($cs) ?>;"></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ===== STAT ROW ===== -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1px;background:#e2e8f0;border-radius:16px;overflow:hidden;margin-bottom:40px;">
            <?php foreach ([[$score.'/100','Overall Score'],[$results['pages_scanned'],'Pages Scanned'],[count($checks),'Checks Run'],[$failCount,'Critical Issues'],[$failCount+$warnCount,'Recommendations']] as $stat): ?>
            <div style="background:#fff;padding:24px;text-align:center;">
                <div style="font-size:1.6rem;font-weight:600;color:#0f172a;"><?= $stat[0] ?></div>
                <div style="font-size:0.75rem;color:#64748b;font-weight:400;margin-top:2px;"><?= $stat[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ===== DIMENSION SCORECARD ===== -->
        <div style="margin-bottom:48px;">
            <div style="font-size:0.72rem;font-weight:500;color:#16a34a;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Scorecard</div>
            <h2 style="font-size:1.6rem;font-weight:600;color:#0f172a;margin-bottom:24px;">Every dimension, one honest verdict.</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
                <?php foreach ($catScores as $cat => $cs): ?>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.9rem;font-weight:500;color:#0f172a;margin-bottom:8px;"><span><?= htmlspecialchars($cat) ?></span><span style="color:<?= auditBarColor($cs) ?>;"><?= $cs ?>/100</span></div>
                    <div style="height:8px;background:#e2e8f0;border-radius:99px;overflow:hidden;"><div style="height:100%;width:<?= $cs ?>%;background:<?= auditBarColor($cs) ?>;"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ===== PRIORITY FINDINGS ===== -->
        <div style="background:#0f172a;border-radius:24px;padding:44px;margin-bottom:48px;">
            <div style="font-size:0.72rem;font-weight:500;color:#4ade80;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Priority Findings</div>
            <h2 style="font-size:1.5rem;font-weight:600;color:#fff;margin-bottom:24px;"><?= min(6,count($priority)) ?> things worth fixing first.</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
                <?php foreach (array_slice($priority, 0, 6) as $c): [$sevLabel,$sevColor] = auditSeverityBadge($c); ?>
                <div style="background:#1e293b;border-radius:14px;padding:20px;">
                    <span style="display:inline-block;font-size:0.65rem;font-weight:500;letter-spacing:.05em;color:<?= $sevColor ?>;background:<?= $sevColor ?>22;padding:3px 10px;border-radius:99px;margin-bottom:10px;"><?= $sevLabel ?></span>
                    <div style="font-weight:500;color:#fff;font-size:0.92rem;margin-bottom:6px;"><?= htmlspecialchars($c['label']) ?></div>
                    <div style="color:#94a3b8;font-size:0.82rem;font-weight:300;line-height:1.5;"><?= htmlspecialchars($c['detail']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="full-report"></div>

        <?php if (!$unlocked): ?>
            <?php if (!$otpPending): ?>
            <!-- ===== EMAIL GATE ===== -->
            <div style="background:#fff;border:2px solid #16a34a;border-radius:20px;padding:36px;max-width:560px;margin:0 auto;">
                <h3 style="font-size:1.15rem;font-weight:600;color:#0f172a;margin-bottom:16px;">Your complete audit contains:</h3>
                <ul style="list-style:none;padding:0;margin:0 0 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;">
                    <?php foreach(['All '.count($checks).' live checks, categorised','AI-written executive summary','Complete issue list, prioritised','Printable / PDF report'] as $item): ?>
                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:0.85rem;font-weight:400;color:#334155;"><i class="fa-solid fa-check" style="color:#16a34a;margin-top:3px;"></i> <?= $item ?></li>
                    <?php endforeach; ?>
                </ul>
                <p style="color:#64748b;font-size:0.85rem;font-weight:400;margin-bottom:16px;">Enter your business email, we'll send a 6-digit code to verify it's really you before unlocking the report.</p>
                <?php if ($error): ?>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px;margin-bottom:16px;">
                    <span style="color:#dc2626;font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>
                <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;">
                    <input type="email" name="unlock_email" placeholder="you@company.com" required
                        style="flex:1;min-width:200px;padding:14px 16px;border-radius:10px;border:1px solid #e2e8f0;font-size:0.95rem;font-weight:400;outline:none;">
                    <button type="submit" name="request_code" value="1" class="tm-btn-green" style="padding:14px 24px;font-weight:500;white-space:nowrap;">Send Verification Code</button>
                </form>
            </div>
            <?php else: ?>
            <!-- ===== VERIFY CODE ===== -->
            <div style="background:#fff;border:2px solid #16a34a;border-radius:20px;padding:36px;max-width:480px;margin:0 auto;text-align:center;">
                <i class="fa-solid fa-envelope-circle-check" style="font-size:1.8rem;color:#16a34a;margin-bottom:12px;"></i>
                <h3 style="font-size:1.1rem;font-weight:600;color:#0f172a;margin-bottom:8px;">Check your inbox</h3>
                <p style="color:#64748b;font-size:0.85rem;font-weight:400;margin-bottom:20px;">We sent a 6-digit code to <strong><?= htmlspecialchars($_SESSION['audit_otp_email']) ?></strong>. Enter it below to unlock your report.</p>
                <?php if ($error): ?>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px;margin-bottom:16px;">
                    <span style="color:#dc2626;font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>
                <form method="POST" style="display:flex;flex-direction:column;gap:12px;align-items:center;">
                    <input type="text" name="otp_code" placeholder="123456" maxlength="6" inputmode="numeric" required
                        style="width:180px;text-align:center;letter-spacing:8px;font-size:1.3rem;font-weight:500;padding:14px;border-radius:10px;border:1px solid #e2e8f0;outline:none;">
                    <button type="submit" name="verify_code" value="1" class="tm-btn-green" style="width:180px;justify-content:center;font-weight:500;">Verify & Unlock</button>
                </form>
                <form method="POST" style="margin-top:14px;">
                    <input type="hidden" name="unlock_email" value="<?= htmlspecialchars($_SESSION['audit_otp_email']) ?>">
                    <button type="submit" name="request_code" value="1" style="background:none;border:none;color:#16a34a;font-size:0.82rem;font-weight:500;cursor:pointer;text-decoration:underline;">Resend code</button>
                </form>
            </div>
            <?php endif; ?>

        <?php else: ?>
        <!-- ===== UNLOCKED ===== -->
        <div style="background:#fff;border:2px solid #16a34a;border-radius:20px;padding:36px;max-width:560px;margin:0 auto;text-align:center;">
            <i class="fa-solid fa-circle-check" style="font-size:1.8rem;color:#16a34a;margin-bottom:12px;"></i>
            <h3 style="font-size:1.15rem;font-weight:600;color:#0f172a;margin-bottom:8px;">Verified. Your full report is ready.</h3>
            <p style="color:#64748b;font-size:0.85rem;font-weight:400;margin-bottom:20px;">It opens in a new tab, including an AI-written executive summary and every check we ran.</p>
            <a href="<?= SITE_URL ?>/tools/audit-report" target="_blank" rel="noopener" id="open-report-btn" class="tm-btn-green" style="font-weight:500;">
                Open Full Report <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i>
            </a>
            <div style="margin-top:16px;">
                <a href="<?= SITE_URL ?>/tools/website-audit?reset=1" style="color:#94a3b8;font-size:0.82rem;font-weight:400;text-decoration:underline;">Scan another site</a>
            </div>
        </div>
        <?php if ($justUnlocked): ?>
        <script>window.open('<?= SITE_URL ?>/tools/audit-report', '_blank');</script>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.audit-page, .audit-page * { font-family: 'Geist', sans-serif; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
