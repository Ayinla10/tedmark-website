<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'Services';
$pageDesc    = 'AI agent development, AI operating systems, AI marketing, strategy, adoption & training, and AI-powered web/app development for growing businesses.';
$pageSeoPage = 'services';

try { $dbServices = fetchAll("SELECT * FROM services WHERE status='active' ORDER BY sort_order ASC"); }
catch(Exception $e) { $dbServices = []; }

require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm2-page-hero">
    <div class="tm2-badge"><span></span> What We Do</div>
    <h1>Everything Your Business Needs to Run on AI</h1>
    <p>End-to-end AI transformation — from strategy through implementation to ongoing support.</p>
</section>

<!-- SERVICES -->
<section class="tm2-section" style="background:var(--bg-soft);">
    <div class="tm2-container">
        <?php
        $servicesFallback = [
            ['num'=>'01','icon'=>'fa-solid fa-robot','color'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)','title'=>'AI Agent Development','description'=>'Build intelligent AI agents for customer support, internal operations, documents, voice, chat, and workflow automation.'],
            ['num'=>'02','icon'=>'fa-solid fa-server','color'=>'#a78bfa','bg'=>'rgba(139,92,246,0.1)','title'=>'AI Operating System','description'=>'Deploy, manage, monitor, and govern every AI agent, workflow, and business knowledge base from one central platform.'],
            ['num'=>'03','icon'=>'fa-solid fa-bullhorn','color'=>'#f43f5e','bg'=>'rgba(244,63,94,0.1)','title'=>'AI Marketing','description'=>'Automate lead generation, CRM, outreach, content, and customer engagement with AI-powered marketing systems.'],
            ['num'=>'04','icon'=>'fa-solid fa-compass','color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)','title'=>'AI Strategy & Consulting','description'=>'Identify high-impact AI opportunities, define implementation roadmaps, and guide your organization from idea to deployment.'],
            ['num'=>'05','icon'=>'fa-solid fa-graduation-cap','color'=>'#14b8a6','bg'=>'rgba(20,184,166,0.1)','title'=>'AI Adoption & Training','description'=>'Equip your teams with practical AI skills, playbooks, and workflows that drive real adoption and measurable productivity.'],
            ['num'=>'06','icon'=>'fa-solid fa-code','color'=>'#60a5fa','bg'=>'rgba(96,165,250,0.1)','title'=>'Web/App Development','description'=>'Design and build AI-powered web and mobile applications using modern technologies like React, Next.js, React Native, etc.'],
        ];
        $services = !empty($dbServices) ? $dbServices : $servicesFallback;
        ?>
        <div class="tm2-grid tm2-grid-3">
        <?php foreach($services as $idx => $svc):
            $feats = array_filter(array_map('trim', explode(',', $svc['features']??'')));
        ?>
        <div class="tm2-card" style="position:relative;">
            <?php $num = $svc['num'] ?? str_pad($idx+1, 2, '0', STR_PAD_LEFT); ?>
            <div style="position:absolute;top:20px;right:22px;font-size:0.78rem;font-weight:800;color:var(--muted);letter-spacing:.05em;"><?= htmlspecialchars($num) ?></div>
            <div class="tm2-card-icon"><i class="<?= htmlspecialchars($svc['icon']??'fa-solid fa-star') ?>"></i></div>
            <h3><?= htmlspecialchars($svc['title']) ?></h3>
            <p style="margin-bottom:16px;"><?= htmlspecialchars($svc['description']??$svc['desc']??'') ?></p>
            <?php if(!empty($feats)): ?>
            <div style="background:var(--bg-soft);border-radius:12px;padding:16px;margin-bottom:16px;">
                <div style="font-size:0.68rem;font-weight:800;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px;">What's included</div>
                <div class="tm2-grid tm2-grid-2" style="gap:8px;">
                    <?php foreach($feats as $feat): ?>
                    <div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--text);">
                        <i class="fa-solid fa-circle-check" style="color:var(--accent);font-size:0.7rem;flex-shrink:0;"></i>
                        <?= htmlspecialchars($feat) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/consultation.php" class="tm2-btn tm2-btn-primary" style="width:100%;justify-content:center;">
                Discuss This Service <i class="fa-solid fa-arrow-right fa-xs"></i>
            </a>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- HOW IT WORKS BAND -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">Our Process</div>
            <h2 class="tm2-h2">Simple, Transparent Process</h2>
        </div>
        <div class="tm2-grid tm2-grid-4">
            <?php
            $steps = [
                ['num'=>'01','icon'=>'fa-solid fa-phone','title'=>'Free Call','desc'=>'30-minute discovery call to understand your needs.'],
                ['num'=>'02','icon'=>'fa-solid fa-file-lines','title'=>'Proposal','desc'=>'Custom proposal with timeline, scope, and pricing.'],
                ['num'=>'03','icon'=>'fa-solid fa-hammer','title'=>'Build','desc'=>'Agile development with weekly progress updates.'],
                ['num'=>'04','icon'=>'fa-solid fa-rocket','title'=>'Launch','desc'=>'Deployment, training, and full handover.'],
            ];
            foreach($steps as $s): ?>
            <div class="tm2-card" style="text-align:center;">
                <div class="tm2-card-icon" style="margin:0 auto 14px;"><i class="<?= $s['icon'] ?>"></i></div>
                <h3><?= $s['title'] ?></h3>
                <p><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
