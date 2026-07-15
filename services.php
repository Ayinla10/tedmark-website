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

<!-- ===== HERO: pillars intro + stats ===== -->
<section class="tm2-section tm2-pillars-hero-section">
    <div class="tm2-container">
        <div class="tm2-pillars-hero">
            <div class="tm2-pillars-hero-copy">
                <div class="tm2-eyebrow">6 Pillars &bull; One Operating System</div>
                <h1 class="tm2-h1">Pick a pillar.<br>Or <em>combine</em> them all.</h1>
                <p class="tm2-sub" style="max-width:520px;">From AI agent development and operating systems to marketing, consulting, training and enablement, and web and mobile — six pillars that mix into whichever combination actually solves your problem.</p>
                <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:28px;">
                    <a href="<?= SITE_URL ?>/consultation.php" class="tm2-btn tm2-btn-primary">Get Started Today</a>
                    <a href="#pillars" class="tm2-btn tm2-btn-outline">Explore the Pillars</a>
                </div>
            </div>
            <div class="tm2-pillars-hero-stats">
                <?php foreach([['2,000+','App Integrations'],['24/7','Automated Operations'],['99.9%','System Reliability']] as $stat): ?>
                <div class="tm2-stat-block">
                    <div class="tm2-stat-num"><?= $stat[0] ?></div>
                    <div class="tm2-stat-lbl"><?= $stat[1] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== SIX PILLARS GRID ===== -->
<section class="tm2-section" id="pillars" style="background:var(--bg-soft);">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <h2 class="tm2-h2">Six specialised pillars.<br><em>Endless combinations.</em></h2>
            <p class="tm2-sub">Each pillar solves a specific business problem, and they combine into one system for maximum impact.</p>
        </div>
        <?php
        $servicesFallback = [
            ['num'=>'01','icon'=>'fa-solid fa-robot','title'=>'AI Agent Development','description'=>'Build intelligent AI agents for customer support, internal operations, documents, voice, chat, and workflow automation.'],
            ['num'=>'02','icon'=>'fa-solid fa-server','title'=>'AI Operating System','description'=>'Deploy, manage, monitor, and govern every AI agent, workflow, and business knowledge base from one central platform.'],
            ['num'=>'03','icon'=>'fa-solid fa-bullhorn','title'=>'AI Marketing','description'=>'Automate lead generation, CRM, outreach, content, and customer engagement with AI-powered marketing systems.'],
            ['num'=>'04','icon'=>'fa-solid fa-compass','title'=>'AI Strategy & Consulting','description'=>'Identify high-impact AI opportunities, define implementation roadmaps, and guide your organization from idea to deployment.'],
            ['num'=>'05','icon'=>'fa-solid fa-graduation-cap','title'=>'AI Adoption & Training','description'=>'Equip your teams with practical AI skills, playbooks, and workflows that drive real adoption and measurable productivity.'],
            ['num'=>'06','icon'=>'fa-solid fa-code','title'=>'Web/App Development','description'=>'Design and build AI-powered web and mobile applications using modern technologies like React, Next.js, React Native, etc.'],
        ];
        $services = !empty($dbServices) ? $dbServices : $servicesFallback;
        ?>
        <div class="tm2-pillars-grid">
        <?php foreach($services as $idx => $svc): $num = $svc['num'] ?? str_pad($idx+1, 2, '0', STR_PAD_LEFT); ?>
        <div class="tm2-pillar-card">
            <span class="tm2-pillar-num"><?= htmlspecialchars($num) ?></span>
            <div class="tm2-pillar-icon"><i class="<?= htmlspecialchars($svc['icon']??'fa-solid fa-star') ?>"></i></div>
            <h3><?= htmlspecialchars($svc['title']) ?></h3>
            <p><?= htmlspecialchars($svc['description']??$svc['desc']??'') ?></p>
            <a href="<?= SITE_URL ?>/consultation.php" class="tm2-pillar-link">Explore Service <i class="fa-solid fa-arrow-right fa-xs"></i></a>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== WHY THESE SERVICES ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-why-head">
            <div>
                <div class="tm2-eyebrow">Why Businesses Choose Tedmark</div>
                <h2 class="tm2-h2">Built for results.<br><em>Built to last.</em></h2>
            </div>
            <div class="tm2-why-tag">Speed, Result, Support</div>
        </div>
        <div class="tm2-why-grid">
            <?php
            $why = [
                ['num'=>'01','title'=>'Practical Implementation','desc'=>'We focus on solutions that solve real business problems — helping you move from ideas to working systems faster.'],
                ['num'=>'02','title'=>'Business-Focused Results','desc'=>'Our goal is not just implementing technology. We help businesses save time, improve efficiency, serve customers better, and create new opportunities.'],
                ['num'=>'03','title'=>'Continuous Support','desc'=>'Technology evolves. Your business evolves. We provide ongoing guidance, improvements, and support to ensure your systems continue delivering value.'],
            ];
            foreach($why as $w): ?>
            <div class="tm2-why-col">
                <span class="tm2-why-num"><?= $w['num'] ?></span>
                <h4><?= $w['title'] ?></h4>
                <p><?= $w['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FAQ ===== -->
<section class="tm2-section" style="background:var(--bg-soft);">
    <div class="tm2-container">
        <div class="tm2-faq-layout">
            <div class="tm2-faq-intro">
                <div class="tm2-eyebrow">Frequently Asked</div>
                <h2 class="tm2-h2">Services, <em>answered</em>.</h2>
                <p class="tm2-sub" style="max-width:360px;">Anything else, book a free 30-minute call. We'll tell you straight which services fit, and which don't.</p>
                <a href="<?= SITE_URL ?>/consultation.php" class="tm2-btn tm2-btn-primary" style="margin-top:20px;">Book a Free Consultation <i class="fa-solid fa-arrow-right fa-xs"></i></a>
            </div>
            <div class="tm2-faq-list">
                <?php
                $faqs = [
                    ['q'=>'Which AI automation service is right for my business?','a'=>'The best service depends on your specific business challenges and goals. We offer a free consultation to analyse your operations and recommend the solutions that will deliver the highest ROI.'],
                    ['q'=>'Can I combine multiple services together?','a'=>'Absolutely. Our pillars are designed to be composable. Many clients start with Strategy & Consulting and then move into Agent Development and an Operating System.'],
                    ['q'=>'How long does it take to implement your services?','a'=>'Initial implementations typically take 4-8 weeks. We focus on rapid, high-impact wins that show value immediately while building for the long term.'],
                    ['q'=>'Do you provide ongoing support after implementation?','a'=>'Yes, all our builds come with structured maintenance and support plans to ensure your AI systems continue to perform as your business grows.'],
                ];
                foreach($faqs as $f): ?>
                <div class="tm2-faq-item">
                    <button type="button" class="tm2-faq-q" onclick="tm2FaqToggle(this)">
                        <span><?= htmlspecialchars($f['q']) ?></span>
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <div class="tm2-faq-a"><p><?= htmlspecialchars($f['a']) ?></p></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<script>
function tm2FaqToggle(btn) {
    var item = btn.closest('.tm2-faq-item');
    var answer = item.querySelector('.tm2-faq-a');
    if (item.classList.contains('open')) {
        answer.style.maxHeight = '0px';
        item.classList.remove('open');
    } else {
        item.classList.add('open');
        answer.style.maxHeight = answer.scrollHeight + 'px';
    }
}
</script>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
