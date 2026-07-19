<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/audit-ai.php';

// Load by persistent token (emailed link) if present, otherwise fall back to session.
$results = null;
if (!empty($_GET['token'])) {
    try {
        $row = fetchOne("SELECT * FROM website_audits WHERE token = ? AND unlocked = 1", [$_GET['token']]);
        if ($row) $results = json_decode($row['results'], true);
    } catch (Exception $e) {}
}
if (!$results && !empty($_SESSION['audit_unlocked']) && !empty($_SESSION['audit_results'])) {
    $results = $_SESSION['audit_results'];
}
if (!$results) {
    header('Location: ' . SITE_URL . '/tools/website-audit');
    exit;
}

$checks    = $results['checks'];
$catScores = $results['category_scores'];
$domain    = parse_url($results['url'], PHP_URL_HOST);
$score     = $results['score'];
$aiReport  = $results['ai_report'] ?? null;

$pageTitle = "Audit Report: $domain";
$pageDesc  = 'Your full website audit report.';

require_once __DIR__ . '/../includes/header.php';

function auditBarColor($s) { return $s >= 80 ? '#16a34a' : ($s >= 55 ? '#f59e0b' : '#ef4444'); }
function auditColorClass($s) { return $s >= 80 ? 'green' : ($s >= 55 ? 'yellow' : 'red'); }
function auditSevBadge($c) {
    if ($c['status'] === 'fail') return ['Critical', '#dc2626', '#fee2e2'];
    if ($c['status'] === 'warn' && $c['weight'] >= 2) return ['High', '#b45309', '#fef3c7'];
    return ['Medium', '#1d4ed8', '#dbeafe'];
}
$categories = [];
foreach ($checks as $c) { $categories[$c['category']][] = $c; }
$failCount = count(array_filter($checks, fn($c) => $c['status'] === 'fail'));
$warnCount = count(array_filter($checks, fn($c) => $c['status'] === 'warn'));
$priority  = array_values(array_filter($checks, fn($c) => $c['status'] !== 'pass'));
usort($priority, fn($a,$b) => $b['weight'] <=> $a['weight']);
$fixNow  = array_values(array_filter($checks, fn($c) => $c['status'] === 'fail'));
$fixSoon = array_values(array_filter($checks, fn($c) => $c['status'] === 'warn' && $c['weight'] >= 2));
$fixLast = array_values(array_filter($checks, fn($c) => $c['status'] === 'warn' && $c['weight'] < 2));
$catIcons = ['SEO'=>'fa-magnifying-glass','Technical'=>'fa-gear','Security'=>'fa-shield-halved','Content'=>'fa-file-lines','Additional Pages'=>'fa-copy'];
?>

<div class="audit-page" style="font-family:'Geist',sans-serif;background:var(--bg-soft);">

<!-- ===== COVER ===== -->
<div style="background:linear-gradient(135deg,#0f172a 0%,#16213e 50%,#0f3460 100%);color:#fff;padding:56px 24px 40px;">
    <div style="max-width:960px;margin:0 auto;">
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(233,69,96,0.15);border:1px solid rgba(233,69,96,0.35);color:#ff8fa3;padding:5px 14px;border-radius:99px;font-size:0.7rem;font-weight:500;letter-spacing:.05em;text-transform:uppercase;margin-bottom:22px;">
            <i class="fa-solid fa-magnifying-glass-chart"></i> Website Audit Report
        </div>
        <h1 style="font-size:1.9rem;font-weight:600;line-height:1.25;margin-bottom:12px;"><?= htmlspecialchars($domain) ?><br><span style="color:#ff6b81;">Comprehensive Digital Audit</span></h1>
        <p style="color:rgba(255,255,255,0.65);font-size:0.92rem;font-weight:300;max-width:600px;margin-bottom:30px;">An in-depth, live-scanned analysis of SEO, technical infrastructure, security, and content, with an AI-written narrative grounded only in what was actually found.</p>
        <div style="display:flex;gap:32px;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,0.15);padding-top:22px;">
            <div><div style="font-size:0.68rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Audited By</div><div style="font-size:0.85rem;font-weight:500;">Tedmark AI Audit Engine</div></div>
            <div><div style="font-size:0.68rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Date</div><div style="font-size:0.85rem;font-weight:500;"><?= date('j F Y') ?></div></div>
            <div><div style="font-size:0.68rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Domain</div><div style="font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($domain) ?></div></div>
            <div><div style="font-size:0.68rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Pages Audited</div><div style="font-size:0.85rem;font-weight:500;"><?= $results['pages_scanned'] ?> key pages</div></div>
            <div><div style="font-size:0.68rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Scope</div><div style="font-size:0.85rem;font-weight:500;"><?= implode(' &middot; ', array_keys($catScores)) ?></div></div>
        </div>
    </div>
</div>

<!-- ===== SCORE BAND ===== -->
<div style="background:#16213e;color:#fff;padding:24px;">
    <div style="max-width:960px;margin:0 auto;display:flex;align-items:center;gap:32px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="width:80px;height:80px;border-radius:50%;border:4px solid <?= auditBarColor($score) ?>;background:<?= auditBarColor($score) ?>1a;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:1.5rem;font-weight:600;color:<?= auditBarColor($score) ?>;line-height:1;"><?= $score ?></span>
                <span style="font-size:0.62rem;color:rgba(255,255,255,0.5);">/100</span>
            </div>
            <div>
                <div style="font-size:1rem;font-weight:500;">Overall: <span style="color:<?= auditBarColor($score) ?>;"><?= $score>=80?'Strong':($score>=55?'Developing':'At Risk') ?></span></div>
                <div style="font-size:0.78rem;color:rgba(255,255,255,0.55);font-weight:300;"><?= $failCount ?> critical &middot; <?= $warnCount ?> to improve, across <?= count($catScores) ?> dimensions</div>
            </div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-left:auto;">
            <?php foreach ($catScores as $cat => $cs): ?>
            <div style="text-align:center;">
                <div style="font-size:1.2rem;font-weight:600;line-height:1;margin-bottom:3px;color:<?= auditBarColor($cs) ?>;"><?= $cs ?></div>
                <div style="font-size:0.65rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:.03em;"><?= htmlspecialchars($cat) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div style="max-width:960px;margin:0 auto;padding:36px 20px 80px;">

    <div class="audit-no-print" style="display:flex;justify-content:flex-end;margin-bottom:20px;">
        <button onclick="window.print()" class="tm-btn-primary" style="font-weight:500;"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    </div>

    <!-- ===== TOC ===== -->
    <div class="audit-print-area" style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:26px 28px;margin-bottom:24px;box-shadow:var(--shadow);">
        <h2 style="font-size:0.95rem;font-weight:600;color:var(--text);margin-bottom:14px;"><i class="fa-solid fa-list-ol" style="color:var(--accent);"></i> Report Contents</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:6px 28px;">
            <div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px dashed var(--border);color:var(--text-soft);font-size:0.82rem;"><span style="background:#16213e;color:#fff;width:20px;height:20px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.62rem;font-weight:600;flex-shrink:0;">1</span> Executive Summary</div>
            <?php foreach (array_keys($catScores) as $i => $cat): ?>
            <div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px dashed var(--border);color:var(--text-soft);font-size:0.82rem;"><span style="background:#16213e;color:#fff;width:20px;height:20px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.62rem;font-weight:600;flex-shrink:0;"><?= $i+2 ?></span> <?= htmlspecialchars($cat) ?></div>
            <?php endforeach; ?>
            <div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px dashed var(--border);color:var(--text-soft);font-size:0.82rem;"><span style="background:#16213e;color:#fff;width:20px;height:20px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.62rem;font-weight:600;flex-shrink:0;"><?= count($catScores)+2 ?></span> Priority Action Plan</div>
        </div>
    </div>

    <!-- ===== 1. EXECUTIVE SUMMARY ===== -->
    <div class="audit-print-area" style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:24px;box-shadow:var(--shadow);">
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px;padding-bottom:14px;border-bottom:2px solid var(--border);">
            <div style="width:38px;height:38px;border-radius:10px;background:var(--accent-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-chart-simple" style="color:var(--accent);"></i></div>
            <div><h2 style="font-size:1.05rem;font-weight:600;color:var(--text);">1. Executive Summary</h2><p style="font-size:0.8rem;color:var(--muted);font-weight:300;">High-level health of <?= htmlspecialchars($domain) ?> across every audited dimension</p></div>
        </div>

        <?php if ($aiReport && !empty($aiReport['executive_summary'])): ?>
        <p style="color:var(--text-soft);font-size:0.9rem;font-weight:300;line-height:1.7;margin-bottom:22px;"><?= htmlspecialchars($aiReport['executive_summary']) ?></p>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:26px;">
            <?php foreach ([[$score.'/100','Overall Score',auditBarColor($score)],[$results['pages_scanned'],'Pages Audited','var(--accent)'],[$failCount,'Critical Issues','#ef4444'],[$failCount+$warnCount,'Recommendations','#3b82f6']] as $k): ?>
            <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:1.5rem;font-weight:600;line-height:1;color:<?= $k[2] ?>;margin-bottom:4px;"><?= $k[0] ?></div>
                <div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;"><?= $k[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <h3 style="font-size:0.88rem;font-weight:600;color:var(--text);margin-bottom:12px;">Score by Dimension</h3>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
            <?php foreach ($catScores as $cat => $cs): ?>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:4px;"><span style="color:var(--text);font-weight:400;"><?= htmlspecialchars($cat) ?></span><span style="font-weight:600;color:<?= auditBarColor($cs) ?>;"><?= $cs ?>/100</span></div>
                <div style="height:7px;background:var(--border);border-radius:99px;overflow:hidden;"><div style="height:100%;width:<?= $cs ?>%;background:<?= auditBarColor($cs) ?>;"></div></div>
            </div>
            <?php endforeach; ?>
        </div>

        <h3 style="font-size:0.88rem;font-weight:600;color:var(--text);margin-bottom:12px;">Top Priorities</h3>
        <?php foreach (array_slice($priority, 0, 5) as $c): [$sevLabel,$sevColor,$sevBg] = auditSevBadge($c); ?>
        <div style="background:<?= $c['status']==='fail'?'#fef2f2':'#fffbeb' ?>;border-left:3px solid <?= $c['status']==='fail'?'#ef4444':'#f59e0b' ?>;border-radius:8px;padding:12px 16px;margin-bottom:8px;">
            <div style="font-weight:500;color:var(--text);font-size:0.85rem;"><?= htmlspecialchars($c['label']) ?></div>
            <div style="color:var(--text-soft);font-size:0.78rem;font-weight:300;margin-top:2px;"><?= htmlspecialchars($c['detail']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== CATEGORY SECTIONS ===== -->
    <?php $sectionNum = 1; foreach ($categories as $catName => $catChecks): $sectionNum++;
        $catPass = array_filter($catChecks, fn($c)=>$c['status']==='pass');
        $catIssues = array_filter($catChecks, fn($c)=>$c['status']!=='pass');
        $catNarrative = $aiReport['category_narratives'][$catName] ?? null;
        $icon = $catIcons[$catName] ?? 'fa-clipboard-check';
    ?>
    <div class="audit-print-area" style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:24px;box-shadow:var(--shadow);">
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px;padding-bottom:14px;border-bottom:2px solid var(--border);">
            <div style="width:38px;height:38px;border-radius:10px;background:var(--accent-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid <?= $icon ?>" style="color:var(--accent);"></i></div>
            <div style="flex:1;"><h2 style="font-size:1.05rem;font-weight:600;color:var(--text);"><?= $sectionNum ?>. <?= htmlspecialchars($catName) ?></h2><p style="font-size:0.8rem;color:var(--muted);font-weight:300;"><?= count($catChecks) ?> checks in this category</p></div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:1.3rem;font-weight:600;color:<?= auditBarColor($catScores[$catName]) ?>;line-height:1;"><?= $catScores[$catName] ?>/100</div>
            </div>
        </div>

        <?php if ($catNarrative): ?>
        <p style="color:var(--text-soft);font-size:0.87rem;font-weight:300;line-height:1.65;margin-bottom:20px;"><?= htmlspecialchars($catNarrative) ?></p>
        <?php endif; ?>

        <?php if (!empty($catPass)): ?>
        <h3 style="font-size:0.85rem;font-weight:600;color:var(--text);margin-bottom:10px;"><i class="fa-solid fa-circle-check" style="color:#16a34a;"></i> What's Working</h3>
        <?php foreach ($catPass as $c): ?>
        <div style="background:#f0fdf4;border-left:3px solid #22c55e;border-radius:8px;padding:11px 15px;margin-bottom:8px;">
            <div style="font-weight:500;color:var(--text);font-size:0.84rem;"><?= htmlspecialchars($c['label']) ?></div>
            <div style="color:var(--text-soft);font-size:0.78rem;font-weight:300;"><?= htmlspecialchars($c['detail']) ?></div>
        </div>
        <?php endforeach; endif; ?>

        <?php if (!empty($catIssues)): ?>
        <h3 style="font-size:0.85rem;font-weight:600;color:var(--text);margin:18px 0 10px;"><i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i> Issues Found</h3>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
            <thead><tr>
                <th style="text-align:left;padding:8px 10px;background:var(--bg-soft);color:var(--muted);font-weight:600;font-size:0.68rem;text-transform:uppercase;letter-spacing:.03em;border-bottom:2px solid var(--border);">Issue</th>
                <th style="text-align:left;padding:8px 10px;background:var(--bg-soft);color:var(--muted);font-weight:600;font-size:0.68rem;text-transform:uppercase;letter-spacing:.03em;border-bottom:2px solid var(--border);">Severity</th>
            </tr></thead>
            <tbody>
            <?php foreach ($catIssues as $c): [$sevLabel,$sevColor,$sevBg] = auditSevBadge($c); ?>
            <tr>
                <td style="padding:10px;border-bottom:1px solid var(--border);vertical-align:top;">
                    <div style="font-weight:500;color:var(--text);"><?= htmlspecialchars($c['label']) ?></div>
                    <div style="color:var(--muted);font-size:0.76rem;font-weight:300;margin-top:2px;"><?= htmlspecialchars($c['detail']) ?></div>
                </td>
                <td style="padding:10px;border-bottom:1px solid var(--border);vertical-align:top;white-space:nowrap;">
                    <span style="display:inline-block;background:<?= $sevBg ?>;color:<?= $sevColor ?>;font-size:0.68rem;font-weight:600;padding:3px 9px;border-radius:99px;"><?= $sevLabel ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- ===== PRIORITY ACTION PLAN ===== -->
    <div class="audit-print-area" style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:24px;box-shadow:var(--shadow);">
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px;padding-bottom:14px;border-bottom:2px solid var(--border);">
            <div style="width:38px;height:38px;border-radius:10px;background:var(--accent-soft);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-list-check" style="color:var(--accent);"></i></div>
            <div><h2 style="font-size:1.05rem;font-weight:600;color:var(--text);"><?= $sectionNum+1 ?>. Priority Action Plan</h2><p style="font-size:0.8rem;color:var(--muted);font-weight:300;">Fix these, in this order</p></div>
        </div>
        <?php if ($aiReport && !empty($aiReport['roadmap_narrative'])): ?>
        <p style="color:var(--text-soft);font-size:0.87rem;font-weight:300;line-height:1.65;margin-bottom:20px;"><?= htmlspecialchars($aiReport['roadmap_narrative']) ?></p>
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
            <?php foreach ([['Fix Now',$fixNow,'#ef4444'],['Fix Soon',$fixSoon,'#f59e0b'],['Worth Considering',$fixLast,'#94a3b8']] as [$label,$items,$color]): ?>
            <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:10px;padding:18px;">
                <div style="font-weight:600;color:var(--text);font-size:0.88rem;margin-bottom:4px;"><?= $label ?></div>
                <div style="font-size:0.7rem;color:var(--muted);margin-bottom:12px;"><?= count($items) ?> item<?= count($items)==1?'':'s' ?></div>
                <?php foreach (array_slice($items,0,6) as $it): ?>
                <div style="display:flex;gap:7px;font-size:0.78rem;color:var(--text-soft);font-weight:300;margin-bottom:7px;"><span style="width:5px;height:5px;border-radius:50%;background:<?= $color ?>;flex-shrink:0;margin-top:6px;"></span><?= htmlspecialchars($it['label']) ?></div>
                <?php endforeach; if (empty($items)): ?><div style="color:var(--muted);font-size:0.78rem;">Nothing here.</div><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($aiReport && !empty($aiReport['closing_note'])): ?>
        <p style="color:var(--text-soft);font-size:0.85rem;font-weight:300;font-style:italic;margin-top:20px;padding-top:16px;border-top:1px solid var(--border);"><?= htmlspecialchars($aiReport['closing_note']) ?></p>
        <?php endif; ?>
    </div>

    <div class="audit-no-print" style="text-align:center;margin-top:8px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/consultation" class="tm-btn-green" style="font-weight:500;">Get Help Fixing These <i class="fa-solid fa-arrow-right fa-xs"></i></a>
        <a href="<?= SITE_URL ?>/tools/website-audit?reset=1" class="tm-btn-outline">Scan Another Site</a>
    </div>

    <div style="text-align:center;padding:24px 0 0;color:var(--muted);font-size:0.72rem;font-weight:300;">
        Independent audit &middot; Prepared <?= date('j F Y') ?> &middot; Not affiliated with the audited domain
    </div>
</div>
</div>

<style>
@media print {
    header, footer, .tm2-nav-wrap, .tm2-announce-bar, .audit-no-print { display: none !important; }
    body { background: #fff !important; }
    .audit-print-area { box-shadow: none !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
