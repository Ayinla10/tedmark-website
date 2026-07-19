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
            $lastErr = error_get_last();
            error_log('Audit OTP mail() failed for ' . $email . ': ' . ($lastErr['message'] ?? 'unknown mail() failure'));
            $error = "We couldn't send a verification email right now. Please try again in a moment, or contact us if this keeps happening.";
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
        $email   = $_SESSION['audit_otp_email'];
        $token   = bin2hex(random_bytes(20));

        require_once __DIR__ . '/../includes/audit-ai.php';
        $aiReport = auditGenerateFullReport(
            $results['url'], $results['checks'], $results['category_scores'],
            $results['page_title'] ?? '', $results['meta_desc'] ?? '', $results['word_count'] ?? 0, $results['pages_scanned'] ?? 1
        );
        $results['ai_report'] = $aiReport;

        try {
            insert('website_audits', [
                'target_url' => $results['url'],
                'email'      => $email,
                'name'       => $_SESSION['audit_otp_name'] ?? '',
                'score'      => $results['score'],
                'token'      => $token,
                'results'    => json_encode($results),
                'unlocked'   => 1,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Exception $e) {}

        $_SESSION['audit_results']  = $results;
        $_SESSION['audit_unlocked'] = true;
        $_SESSION['audit_token']    = $token;
        $justUnlocked = true;
        unset($_SESSION['audit_otp_pending'], $_SESSION['audit_otp_code']);

        $reportUrl = SITE_URL . '/tools/audit-report?token=' . $token;
        $domain = parse_url($results['url'], PHP_URL_HOST);
        $summarySnippet = $aiReport['executive_summary'] ?? "Your {$results['score']}/100 audit for $domain is ready.";
        sendMail($email, "Your website audit report for $domain",
            "<p>Your full website audit report is ready.</p>
             <p style='background:#f8fafc;border-radius:8px;padding:16px;color:#334155;'>$summarySnippet</p>
             <p><a href='" . htmlspecialchars($reportUrl) . "' style='background:#16a34a;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;display:inline-block;'>Open Full Report</a></p>
             <p style='color:#94a3b8;font-size:13px;'>Or copy this link: " . htmlspecialchars($reportUrl) . "</p>");
    }
}

$results    = $_SESSION['audit_results'] ?? null;
$otpPending = !empty($_SESSION['audit_otp_pending']);
$unlocked   = !empty($_SESSION['audit_unlocked']);

require_once __DIR__ . '/../includes/header.php';

function auditBarColor($score) {
    return $score >= 80 ? '#16a34a' : ($score >= 55 ? '#f59e0b' : '#ef4444');
}
function auditSeverityBadge($c) {
    if ($c['status'] === 'fail') return ['CRITICAL', '#ef4444'];
    if ($c['status'] === 'warn' && $c['weight'] >= 2) return ['HIGH', '#f59e0b'];
    return ['MEDIUM', '#94a3b8'];
}
?>

<?php if (!$results): ?>
<!-- ===== HERO (no scan yet) ===== -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container">
        <div class="tm-page-hero-inner" style="text-align:center;max-width:640px;margin:0 auto;">
            <div class="tm-badge tm-fade" style="animation-delay:.05s">
                <i class="fa-solid fa-magnifying-glass-chart"></i> Free AI Website Audit
            </div>
            <h1 class="tm-page-hero-title tm-fade" style="animation-delay:.1s"><?= toolcfg($cfg,'tool_audit_h1','Find Out What\'s Costing You Customers') ?></h1>
            <p class="tm-page-hero-desc tm-fade" style="animation-delay:.15s">
                <?= toolcfg($cfg,'tool_audit_subtext','A real, live crawl of your entire site\'s SEO, performance, and security, checking every page we can find.') ?>
            </p>
        </div>
    </div>
</section>

<section class="audit-page" style="padding:60px 16px 96px;background:var(--bg-soft);">
    <div style="max-width:640px;margin:0 auto;background:var(--card);border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow);padding:40px;">
        <?php if ($error): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:20px;">
            <span style="color:#dc2626;font-size:0.875rem;font-weight:500;"><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>
        <form method="POST" id="audit-form">
            <label style="display:block;font-size:13px;font-weight:400;color:var(--text-soft);margin-bottom:10px;text-transform:uppercase;letter-spacing:.04em;">Website URL</label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <input type="text" name="scan_url" placeholder="https://www.company.com" required
                    style="flex:1;min-width:220px;padding:16px 18px;border:2px solid var(--border);border-radius:12px;font-size:16px;font-weight:400;color:var(--text);background:var(--bg);box-sizing:border-box;outline:none;">
                <button type="submit" id="audit-submit" class="tm-btn-green" style="padding:16px 28px;font-size:1rem;font-weight:500;white-space:nowrap;">
                    <span id="audit-submit-label">Start Free AI Audit</span> <i class="fa-solid fa-arrow-right fa-xs"></i>
                </button>
            </div>
            <p style="font-size:0.78rem;color:var(--muted);margin-top:12px;">We crawl your whole site (up to 12 pages), checking every one and testing every internal link for dead URLs. Usually takes 20-40 seconds.</p>
        </form>
    </div>
</section>
<script>
document.getElementById('audit-form').addEventListener('submit', function(){
    document.getElementById('audit-submit-label').textContent = 'Crawling your site...';
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
    $fixNow    = array_values(array_filter($checks, fn($c) => $c['status'] === 'fail'));
    $fixSoon   = array_values(array_filter($checks, fn($c) => $c['status'] === 'warn' && $c['weight'] >= 2));
    $worthIt   = array_values(array_filter($checks, fn($c) => $c['status'] === 'warn' && $c['weight'] < 2));
?>
<section class="audit-page tm2-pillars-hero-section" style="background:var(--bg-soft);">

    <!-- ===== MINI NAV ===== -->
    <div style="background:var(--card);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:8px;font-weight:500;color:var(--text);font-size:0.9rem;">
            <span style="width:22px;height:22px;border-radius:6px;background:var(--accent);color:var(--accent-ink);display:inline-flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:600;">A</span>
            Audit / <?= htmlspecialchars($domain) ?>
        </div>
        <div style="display:flex;gap:20px;margin-left:auto;flex-wrap:wrap;">
            <a href="#scorecard" style="color:var(--text-soft);font-size:0.85rem;text-decoration:none;">Scorecard</a>
            <a href="#findings" style="color:var(--text-soft);font-size:0.85rem;text-decoration:none;">Findings</a>
            <a href="#checked" style="color:var(--text-soft);font-size:0.85rem;text-decoration:none;">Contents</a>
            <a href="#roadmap" style="color:var(--text-soft);font-size:0.85rem;text-decoration:none;">Roadmap</a>
        </div>
        <a href="#unlock" style="background:var(--text);color:var(--bg);font-size:0.8rem;font-weight:500;padding:8px 16px;border-radius:99px;text-decoration:none;white-space:nowrap;">Read report <i class="fa-solid fa-arrow-right fa-2xs"></i></a>
    </div>

    <!-- ===== HERO / COVER ===== -->
    <div style="padding:56px 24px 40px;max-width:1000px;margin:0 auto;display:grid;grid-template-columns:1.3fr 1fr;gap:32px;align-items:start;">
        <div>
            <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(220,38,38,0.1);color:#ef4444;font-size:0.7rem;font-weight:500;letter-spacing:.06em;text-transform:uppercase;padding:5px 14px;border-radius:99px;margin-bottom:20px;">
                <i class="fa-solid fa-circle-dot fa-2xs"></i> Independent Audit &middot; <?= date('F Y') ?>
            </div>
            <h1 style="font-size:2.1rem;font-weight:600;line-height:1.2;color:var(--text);margin-bottom:14px;">A live look at<br><em style="color:#ef4444;font-style:italic;font-weight:500;"><?= htmlspecialchars($domain) ?></em></h1>
            <p style="color:var(--text-soft);font-size:0.92rem;font-weight:300;line-height:1.7;margin-bottom:24px;max-width:480px;">
                <?= count($checks) ?> checks across <?= $results['pages_scanned'] ?> page<?= $results['pages_scanned']==1?'':'s' ?>, <?= $failCount ?> critical issue<?= $failCount==1?'':'s' ?>, <?= $failCount+$warnCount ?> recommendations, costing you leads and search visibility today.
            </p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="#unlock" class="tm-btn-primary" style="font-weight:500;">Read the full report <i class="fa-solid fa-arrow-right fa-xs"></i></a>
                <a href="<?= SITE_URL ?>/tools/website-audit?reset=1" class="tm-btn-outline">Scan a different site</a>
            </div>
            <div style="display:flex;gap:32px;flex-wrap:wrap;border-top:1px solid var(--border);padding-top:20px;margin-top:32px;">
                <div><div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Domain</div><div style="font-size:0.85rem;font-weight:500;color:var(--text);"><?= htmlspecialchars($domain) ?></div></div>
                <div><div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Date</div><div style="font-size:0.85rem;font-weight:500;color:var(--text);"><?= date('F Y') ?></div></div>
                <div><div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Pages Scanned</div><div style="font-size:0.85rem;font-weight:500;color:var(--text);"><?= $results['pages_scanned'] ?></div></div>
            </div>
        </div>

        <!-- score card (always dark, matches other dark-hero accents on the site) -->
        <div style="background:#0f172a;border-radius:18px;padding:28px;">
            <div style="font-size:0.68rem;color:#94a3b8;font-weight:400;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Overall Score</div>
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px;">
                <div style="font-size:2.6rem;font-weight:600;color:#fff;line-height:1;"><?= $score ?><span style="font-size:1.1rem;color:#64748b;font-weight:300;">/100</span></div>
                <div style="width:50px;height:50px;border-radius:50%;border:4px solid <?= auditBarColor($score) ?>;flex-shrink:0;"></div>
            </div>
            <div style="display:inline-block;background:rgba(74,222,128,0.12);color:#4ade80;font-size:0.7rem;font-weight:500;padding:4px 12px;border-radius:99px;margin-bottom:20px;"><?= $statusLabel ?></div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($catScores as $cat => $cs): ?>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:#94a3b8;font-weight:300;margin-bottom:4px;"><span><?= htmlspecialchars($cat) ?></span><span><?= $cs ?>/100</span></div>
                    <div style="height:5px;background:#334155;border-radius:99px;overflow:hidden;"><div style="height:100%;width:<?= $cs ?>%;background:<?= auditBarColor($cs) ?>;"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===== UNLOCK (right after hero, no scrolling needed) ===== -->
    <div id="unlock" style="background:linear-gradient(120deg,#0f172a 0%,#1e1029 100%);padding:40px 24px;">
        <div style="max-width:560px;margin:0 auto;text-align:center;">
            <?php if ($unlocked): ?>
            <?php $reportLink = SITE_URL . '/tools/audit-report' . (!empty($_SESSION['audit_token']) ? '?token=' . $_SESSION['audit_token'] : ''); ?>
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(74,222,128,0.12);border:1px solid rgba(74,222,128,0.3);color:#4ade80;font-size:0.82rem;font-weight:500;padding:8px 16px;border-radius:99px;margin-bottom:16px;">
                <i class="fa-solid fa-envelope-circle-check"></i> Sent to your email &mdash; check your inbox for the report link
            </div>
            <p style="color:#e2e8f0;font-size:0.95rem;font-weight:500;margin-bottom:16px;">Verified. Your full report is ready.</p>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                <a href="<?= htmlspecialchars($reportLink) ?>" target="_blank" rel="noopener" class="tm-btn-primary" style="font-weight:500;background:#f472b6;">Open full report <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i></a>
                <a href="<?= SITE_URL ?>/tools/website-audit?reset=1" style="color:#94a3b8;font-size:0.85rem;font-weight:400;align-self:center;text-decoration:underline;">Scan another site</a>
            </div>
            <?php if ($justUnlocked): ?><script>window.open('<?= htmlspecialchars($reportLink) ?>', '_blank');</script><?php endif; ?>

            <?php elseif ($otpPending): ?>
            <p style="color:#e2e8f0;font-size:0.9rem;font-weight:500;margin-bottom:6px;">Check your inbox</p>
            <?php if ($error): ?>
            <div style="background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);border-radius:10px;padding:12px;margin:10px 0;text-align:left;">
                <span style="color:#fca5a5;font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            <p style="color:#94a3b8;font-size:0.82rem;font-weight:300;margin-bottom:14px;">6-digit code sent to <strong style="color:#e2e8f0;"><?= htmlspecialchars($_SESSION['audit_otp_email']) ?></strong></p>
            <form method="POST" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;align-items:center;">
                <input type="text" name="otp_code" placeholder="123456" maxlength="6" inputmode="numeric" required autofocus
                    style="width:150px;text-align:center;letter-spacing:6px;font-size:1.15rem;font-weight:500;padding:12px;border-radius:10px;border:1px solid #334155;background:#1e293b;color:#fff;outline:none;">
                <button type="submit" name="verify_code" value="1" class="tm-btn-primary" style="font-weight:500;background:#f472b6;">Verify & Unlock</button>
            </form>
            <form method="POST" style="margin-top:10px;">
                <input type="hidden" name="unlock_email" value="<?= htmlspecialchars($_SESSION['audit_otp_email']) ?>">
                <button type="submit" name="request_code" value="1" style="background:none;border:none;color:#94a3b8;font-size:0.78rem;font-weight:400;cursor:pointer;text-decoration:underline;">Resend code</button>
            </form>

            <?php else: ?>
            <p style="color:#fff;font-size:0.95rem;font-weight:500;margin-bottom:4px;">Get the full report + AI summary</p>
            <p style="color:#94a3b8;font-size:0.8rem;font-weight:300;margin-bottom:14px;">We'll verify your email with a 6-digit code, then send you the report link.</p>
            <?php if ($error): ?>
            <div style="background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);border-radius:10px;padding:12px;margin-bottom:14px;text-align:left;">
                <span style="color:#fca5a5;font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
                <input type="email" name="unlock_email" placeholder="you@company.com" required autofocus
                    style="flex:1;min-width:200px;max-width:280px;padding:12px 14px;border-radius:10px;border:1px solid #334155;background:#1e293b;color:#fff;font-size:0.9rem;font-weight:400;outline:none;">
                <button type="submit" name="request_code" value="1" class="tm-btn-primary" style="font-weight:500;white-space:nowrap;background:#f472b6;">Send Code</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== STAT BAND ===== -->
    <div style="background:var(--card);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
        <div style="max-width:1000px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1px;background:var(--border);">
            <?php foreach ([[$score.'/100','Overall Score',$statusLabel],[$results['pages_scanned'],'Pages Scanned','All key pages'],[$failCount,'Critical Issues','Immediate action'],[$failCount+$warnCount,'Recommendations','Prioritised']] as $stat): ?>
            <div style="background:var(--card);padding:28px 20px;">
                <div style="font-size:1.7rem;font-weight:600;color:var(--text);"><?= $stat[0] ?></div>
                <div style="font-size:0.8rem;color:var(--text-soft);font-weight:400;margin-top:2px;"><?= $stat[1] ?></div>
                <div style="font-size:0.7rem;color:var(--muted);font-weight:300;margin-top:2px;"><?= $stat[2] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===== SCORECARD ===== -->
    <div id="scorecard" style="max-width:1000px;margin:0 auto;padding:64px 24px;">
        <div style="font-size:0.72rem;font-weight:400;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">&mdash; 01 &middot; Scorecard</div>
        <h2 style="font-size:1.7rem;font-weight:600;color:var(--text);margin-bottom:10px;">Every dimension, one honest verdict.</h2>
        <p style="color:var(--text-soft);font-size:0.9rem;font-weight:300;max-width:560px;margin-bottom:32px;">A dimension scores well when most of its checks pass. The fixes below are known, sequenceable, and mostly quick wins.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px 40px;">
            <?php foreach ($catScores as $cat => $cs): ?>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:0.9rem;font-weight:500;color:var(--text);margin-bottom:8px;"><span><?= htmlspecialchars($cat) ?></span><span style="color:<?= auditBarColor($cs) ?>;"><?= $cs ?>/100</span></div>
                <div style="height:7px;background:var(--border);border-radius:99px;overflow:hidden;"><div style="height:100%;width:<?= $cs ?>%;background:<?= auditBarColor($cs) ?>;"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===== PRIORITY FINDINGS (always dark) ===== -->
    <div id="findings" style="background:#0f172a;padding:64px 24px;">
        <div style="max-width:1000px;margin:0 auto;">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
                <div>
                    <div style="font-size:0.72rem;font-weight:400;color:#4ade80;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">&mdash; 02 &middot; Priority Findings</div>
                    <h2 style="font-size:1.6rem;font-weight:600;color:#fff;"><?= min(6,count($priority)) ?> things costing you leads.</h2>
                </div>
                <div style="color:#64748b;font-size:0.8rem;font-weight:300;"><?= $failCount ?> critical &middot; <?= $warnCount ?> recommendations</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px 40px;">
                <?php foreach (array_slice($priority, 0, 6) as $i => $c): [$sevLabel,$sevColor] = auditSeverityBadge($c); ?>
                <div style="border-bottom:1px solid #1e293b;padding-bottom:20px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                        <span style="font-size:0.62rem;font-weight:500;letter-spacing:.05em;color:<?= $sevColor ?>;background:<?= $sevColor ?>1a;padding:3px 9px;border-radius:99px;"><?= $sevLabel ?></span>
                        <span style="font-size:0.68rem;color:#64748b;font-weight:300;">Finding <?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></span>
                    </div>
                    <div style="font-weight:500;color:#fff;font-size:0.95rem;margin-bottom:6px;"><?= htmlspecialchars($c['label']) ?></div>
                    <div style="color:#94a3b8;font-size:0.82rem;font-weight:300;line-height:1.5;"><?= htmlspecialchars($c['detail']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===== WHAT WE CHECKED (contents-style) ===== -->
    <div id="checked" style="max-width:1000px;margin:0 auto;padding:64px 24px;">
        <div style="display:grid;grid-template-columns:1fr 1.3fr;gap:40px;">
            <div>
                <div style="font-size:0.72rem;font-weight:400;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">&mdash; 03 &middot; Contents</div>
                <h2 style="font-size:1.5rem;font-weight:600;color:var(--text);margin-bottom:10px;">What's inside the report.</h2>
                <p style="color:var(--text-soft);font-size:0.88rem;font-weight:300;margin-bottom:20px;"><?= count($checks) ?> checks, every finding cited to a specific check. Numbered so you can share and reference clearly.</p>
                <a href="#findings" class="tm-btn-outline">Start reading</a>
            </div>
            <div>
                <?php $ci=0; foreach ($catScores as $cat => $cs):
                    $catCount = count(array_filter($checks, fn($c) => $c['category']===$cat));
                    $ci++;
                ?>
                <div style="display:flex;align-items:center;gap:16px;padding:16px 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:0.78rem;color:var(--muted);font-weight:400;width:24px;flex-shrink:0;"><?= str_pad($ci,2,'0',STR_PAD_LEFT) ?></span>
                    <div style="flex:1;">
                        <div style="font-weight:500;color:var(--text);font-size:0.9rem;"><?= htmlspecialchars($cat) ?></div>
                        <div style="color:var(--muted);font-size:0.78rem;font-weight:300;"><?= $catCount ?> checks &middot; score <?= $cs ?>/100</div>
                    </div>
                    <i class="fa-solid fa-arrow-right fa-xs" style="color:var(--muted);"></i>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===== PRIORITY ACTION PLAN ===== -->
    <div id="roadmap" style="background:var(--card);border-top:1px solid var(--border);padding:64px 24px;">
        <div style="max-width:1000px;margin:0 auto;">
            <div style="font-size:0.72rem;font-weight:400;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">&mdash; 04 &middot; Priority Action Plan</div>
            <h2 style="font-size:1.6rem;font-weight:600;color:var(--text);margin-bottom:28px;">Fix these, in this order.</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;">
                <?php foreach ([['Fix Now', $fixNow, '#ef4444', 'fa-triangle-exclamation'], ['Fix Soon', $fixSoon, '#f59e0b', 'fa-clock'], ['Worth Considering', $worthIt, '#94a3b8', 'fa-circle-check']] as [$label, $items, $color, $icon]): ?>
                <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:14px;padding:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                        <span style="font-size:0.68rem;color:var(--muted);font-weight:400;text-transform:uppercase;letter-spacing:.05em;"><?= count($items) ?> item<?= count($items)==1?'':'s' ?></span>
                        <i class="fa-solid <?= $icon ?>" style="color:<?= $color ?>;font-size:0.9rem;"></i>
                    </div>
                    <div style="font-weight:600;color:var(--text);font-size:1rem;margin-bottom:14px;"><?= $label ?></div>
                    <?php if (empty($items)): ?>
                    <div style="color:var(--muted);font-size:0.82rem;font-weight:300;">Nothing here.</div>
                    <?php else: foreach (array_slice($items,0,5) as $it): ?>
                    <div style="display:flex;align-items:flex-start;gap:8px;font-size:0.83rem;color:var(--text-soft);font-weight:300;margin-bottom:8px;">
                        <span style="width:5px;height:5px;border-radius:50%;background:<?= $color ?>;flex-shrink:0;margin-top:7px;"></span>
                        <?= htmlspecialchars($it['label']) ?>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER META ===== -->
    <div style="max-width:1000px;margin:0 auto;padding:20px 24px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <span style="font-size:0.75rem;color:var(--muted);font-weight:300;">Independent audit &middot; Prepared <?= date('F Y') ?> &middot; Not affiliated with the audited domain</span>
        <span style="font-size:0.75rem;color:var(--muted);font-weight:300;">Report v1.0 &middot; <?= count($catScores) ?> sections &middot; <?= $failCount+$warnCount ?> recommendations</span>
    </div>
</section>
<?php endif; ?>

<style>
.audit-report, .audit-report * { font-family: 'Geist', sans-serif; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
