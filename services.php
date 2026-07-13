<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'Services';
$pageDesc    = 'Business technology solutions for growing companies — systems, automation, web development, e-commerce, digital marketing, and branding.';
$pageSeoPage = 'services';

try { $dbServices = fetchAll("SELECT * FROM services WHERE status='active' ORDER BY sort_order ASC"); }
catch(Exception $e) { $dbServices = []; }

require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm2-page-hero">
    <div class="tm2-badge"><span></span> What We Do</div>
    <h1>Everything Your Business Needs to Thrive Digitally</h1>
    <p>End-to-end digital transformation — from strategy through implementation to ongoing support.</p>
</section>

<!-- SERVICES -->
<section class="tm2-section" style="background:var(--bg-soft);">
    <div class="tm2-container">
        <?php
        $servicesFallback = [
            ['icon'=>'fa-solid fa-gears','color'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)','title'=>'Business Systems','subtitle'=>'ERPs, CRMs & Operational Platforms','description'=>'Custom-built systems that replace spreadsheets, manual records, and disconnected apps. We build ERPs, inventory management, HR systems, customer relationship platforms, and any workflow-specific tools your business needs.','features'=>'Custom ERP development,Inventory & stock management,HR & payroll systems,Customer management (CRM),Multi-branch management,Reporting & dashboards'],
            ['icon'=>'fa-solid fa-robot','color'=>'#a78bfa','bg'=>'rgba(139,92,246,0.1)','title'=>'Automation','subtitle'=>'Workflows, Triggers & Scheduled Tasks','description'=>'We automate the repetitive, time-consuming tasks that are eating your team\'s productivity. From invoice generation to report delivery, customer follow-ups to data syncing — if it\'s repetitive, we can automate it.','features'=>'Invoice & payment automation,Email & SMS campaigns,Report generation & delivery,Data sync between platforms,Approval workflow automation,API integrations'],
            ['icon'=>'fa-solid fa-code','color'=>'#60a5fa','bg'=>'rgba(96,165,250,0.1)','title'=>'Web Development','subtitle'=>'Sites, Portals & Web Applications','description'=>'Fast, modern websites and web applications that look great, perform excellently, and convert visitors into customers. Built with clean code, optimised for mobile, and designed to rank on search engines.','features'=>'Corporate & business websites,Web applications & portals,Landing pages,Progressive web apps,WordPress development,Performance optimisation'],
            ['icon'=>'fa-solid fa-cart-shopping','color'=>'#fb923c','bg'=>'rgba(251,146,60,0.1)','title'=>'E-Commerce','subtitle'=>'Online Stores & Marketplaces','description'=>'We build online stores that actually sell — with seamless checkout, local payment integration (MTN MoMo, Vodafone Cash, bank cards), inventory sync, and delivery management built in.','features'=>'Custom online stores,Mobile-first design,Local payment gateways,Inventory management,Order & delivery tracking,Multi-vendor marketplaces'],
            ['icon'=>'fa-solid fa-bullhorn','color'=>'#f43f5e','bg'=>'rgba(244,63,94,0.1)','title'=>'Digital Marketing','subtitle'=>'SEO, Social Media & Paid Ads','description'=>'We help you get found online, build a following, and convert traffic into revenue. Strategy-led digital marketing campaigns that deliver measurable results.','features'=>'Search engine optimisation,Social media management,Google & Meta ad campaigns,Content marketing,Email marketing,Analytics & reporting'],
            ['icon'=>'fa-solid fa-palette','color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)','title'=>'Branding & Design','subtitle'=>'Identity, UI/UX & Visual Systems','description'=>'Professional brand identity that builds trust and stands out in your market. From logo design to full visual systems, pitch decks, and marketing materials — we make your business look world-class.','features'=>'Logo & brand identity,UI/UX design,Brand guidelines,Marketing collateral,Pitch deck design,Social media templates'],
            ['icon'=>'fa-solid fa-headset','color'=>'#14b8a6','bg'=>'rgba(20,184,166,0.1)','title'=>'IT Consulting','subtitle'=>'Strategy, Audits & Advisory','description'=>'Not sure where to start? We help you map out your digital journey — auditing your current setup, identifying gaps, and creating a prioritised roadmap for digital transformation.','features'=>'Digital transformation strategy,IT infrastructure audit,Technology stack advice,Vendor selection,Change management,Staff training'],
        ];
        $services = !empty($dbServices) ? $dbServices : $servicesFallback;
        ?>
        <div class="tm2-grid tm2-grid-3">
        <?php foreach($services as $idx => $svc):
            $feats = array_filter(array_map('trim', explode(',', $svc['features']??'')));
        ?>
        <div class="tm2-card">
            <div class="tm2-card-icon"><i class="<?= htmlspecialchars($svc['icon']??'fa-solid fa-star') ?>"></i></div>
            <h3><?= htmlspecialchars($svc['title']) ?></h3>
            <p style="margin-bottom:16px;"><?= htmlspecialchars($svc['description']??$svc['desc']??'') ?></p>
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
