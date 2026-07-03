<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'Services';
$pageDesc    = 'Business technology solutions for African companies — systems, automation, web development, e-commerce, digital marketing, and branding.';
$pageSeoPage = 'services';

try { $dbServices = fetchAll("SELECT * FROM services WHERE status='active' ORDER BY sort_order ASC"); }
catch(Exception $e) { $dbServices = []; }

require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">What We Do</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.15;">Everything Your Business<br>Needs to Thrive Digitally</h1>
        <p style="font-size:1.1rem;color:#94a3b8;max-width:600px;margin:0 auto;line-height:1.7;">End-to-end digital transformation — from strategy through implementation to ongoing support.</p>
    </div>
</section>

<!-- SERVICES -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <?php
        $servicesFallback = [
            ['icon'=>'fa-solid fa-gears','color'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)','title'=>'Business Systems','subtitle'=>'ERPs, CRMs & Operational Platforms','description'=>'Custom-built systems that replace spreadsheets, manual records, and disconnected apps. We build ERPs, inventory management, HR systems, customer relationship platforms, and any workflow-specific tools your business needs.','features'=>'Custom ERP development,Inventory & stock management,HR & payroll systems,Customer management (CRM),Multi-branch management,Reporting & dashboards'],
            ['icon'=>'fa-solid fa-robot','color'=>'#a78bfa','bg'=>'rgba(139,92,246,0.1)','title'=>'Automation','subtitle'=>'Workflows, Triggers & Scheduled Tasks','description'=>'We automate the repetitive, time-consuming tasks that are eating your team\'s productivity. From invoice generation to report delivery, customer follow-ups to data syncing — if it\'s repetitive, we can automate it.','features'=>'Invoice & payment automation,Email & SMS campaigns,Report generation & delivery,Data sync between platforms,Approval workflow automation,API integrations'],
            ['icon'=>'fa-solid fa-code','color'=>'#60a5fa','bg'=>'rgba(96,165,250,0.1)','title'=>'Web Development','subtitle'=>'Sites, Portals & Web Applications','description'=>'Fast, modern websites and web applications that look great, perform excellently, and convert visitors into customers. Built with clean code, optimised for mobile, and designed to rank on search engines.','features'=>'Corporate & business websites,Web applications & portals,Landing pages,Progressive web apps,WordPress development,Performance optimisation'],
            ['icon'=>'fa-solid fa-cart-shopping','color'=>'#fb923c','bg'=>'rgba(251,146,60,0.1)','title'=>'E-Commerce','subtitle'=>'Online Stores & Marketplaces','description'=>'We build online stores that actually sell — with seamless checkout, local payment integration (MTN MoMo, Vodafone Cash, bank cards), inventory sync, and delivery management built in.','features'=>'Custom online stores,Mobile-first design,Local payment gateways,Inventory management,Order & delivery tracking,Multi-vendor marketplaces'],
            ['icon'=>'fa-solid fa-bullhorn','color'=>'#f43f5e','bg'=>'rgba(244,63,94,0.1)','title'=>'Digital Marketing','subtitle'=>'SEO, Social Media & Paid Ads','description'=>'We help you get found online, build a following, and convert traffic into revenue. Strategy-led digital marketing campaigns that deliver measurable results for African businesses.','features'=>'Search engine optimisation,Social media management,Google & Meta ad campaigns,Content marketing,Email marketing,Analytics & reporting'],
            ['icon'=>'fa-solid fa-palette','color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)','title'=>'Branding & Design','subtitle'=>'Identity, UI/UX & Visual Systems','description'=>'Professional brand identity that builds trust and stands out in your market. From logo design to full visual systems, pitch decks, and marketing materials — we make your business look world-class.','features'=>'Logo & brand identity,UI/UX design,Brand guidelines,Marketing collateral,Pitch deck design,Social media templates'],
            ['icon'=>'fa-solid fa-headset','color'=>'#14b8a6','bg'=>'rgba(20,184,166,0.1)','title'=>'IT Consulting','subtitle'=>'Strategy, Audits & Advisory','description'=>'Not sure where to start? We help you map out your digital journey — auditing your current setup, identifying gaps, and creating a prioritised roadmap for digital transformation.','features'=>'Digital transformation strategy,IT infrastructure audit,Technology stack advice,Vendor selection,Change management,Staff training'],
        ];
        $services = !empty($dbServices) ? $dbServices : $servicesFallback;
        foreach($services as $idx => $svc):
            $feats = array_filter(array_map('trim', explode(',', $svc['features']??'')));
            $bg    = $svc['bg'] ?? 'rgba(34,197,94,0.1)';
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center;padding:64px 0;<?= $idx > 0 ? 'border-top:1px solid #f1f5f9;' : '' ?>" class="tm-svc-row tm-fade">
            <div style="<?= $idx % 2 === 1 ? 'order:2;' : '' ?>">
                <div style="display:inline-flex;align-items:center;gap:12px;margin-bottom:20px;">
                    <div style="width:52px;height:52px;border-radius:14px;background:<?= htmlspecialchars($bg) ?>;display:flex;align-items:center;justify-content:center;">
                        <i class="<?= htmlspecialchars($svc['icon']??'fa-solid fa-star') ?>" style="font-size:1.4rem;color:<?= htmlspecialchars($svc['color']??'#22c55e') ?>;"></i>
                    </div>
                    <div>
                        <div class="tm-label" style="margin-bottom:0;"><?= htmlspecialchars($svc['title']) ?></div>
                        <div style="font-size:0.8rem;color:#94a3b8;"><?= htmlspecialchars($svc['subtitle']??'') ?></div>
                    </div>
                </div>
                <p style="font-size:1rem;color:#334155;line-height:1.75;margin-bottom:28px;"><?= htmlspecialchars($svc['description']??$svc['desc']??'') ?></p>
                <a href="<?= SITE_URL ?>/consultation.php" class="tm-btn-green">
                    Discuss This Service <i class="fa-solid fa-arrow-right fa-xs"></i>
                </a>
            </div>
            <div style="<?= $idx % 2 === 1 ? 'order:1;' : '' ?>">
                <div style="background:#fff;border:1.5px solid #f1f5f9;border-radius:16px;padding:28px;box-shadow:0 4px 24px rgba(0,0,0,0.04);">
                    <div style="font-size:0.75rem;font-weight:800;color:#64748b;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px;">What's included</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <?php foreach($feats as $feat): ?>
                        <div style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:#374151;">
                            <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:0.75rem;flex-shrink:0;"></i>
                            <?= htmlspecialchars($feat) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- HOW IT WORKS BAND -->
<section style="padding:80px 0;background:#fff;">
    <div class="tm-container">
        <div style="text-align:center;max-width:600px;margin:0 auto 48px;">
            <div class="tm-label">Our Process</div>
            <h2 class="tm-section-title">Simple, Transparent Process</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px;">
            <?php
            $steps = [
                ['num'=>'01','icon'=>'fa-solid fa-phone','title'=>'Free Call','desc'=>'30-minute discovery call to understand your needs.'],
                ['num'=>'02','icon'=>'fa-solid fa-file-lines','title'=>'Proposal','desc'=>'Custom proposal with timeline, scope, and pricing.'],
                ['num'=>'03','icon'=>'fa-solid fa-hammer','title'=>'Build','desc'=>'Agile development with weekly progress updates.'],
                ['num'=>'04','icon'=>'fa-solid fa-rocket','title'=>'Launch','desc'=>'Deployment, training, and full handover.'],
                ['num'=>'05','icon'=>'fa-solid fa-headset','title'=>'Support','desc'=>'Ongoing maintenance and support packages.'],
            ];
            foreach($steps as $s): ?>
            <div class="tm-fade" style="text-align:center;">
                <div class="tm-step-num"><?= $s['num'] ?></div>
                <div style="width:44px;height:44px;border-radius:12px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:12px auto;">
                    <i class="<?= $s['icon'] ?>" style="color:#16a34a;font-size:1.1rem;"></i>
                </div>
                <h3 style="font-size:0.95rem;font-weight:700;color:#0f172a;margin-bottom:6px;"><?= $s['title'] ?></h3>
                <p style="font-size:0.82rem;color:#64748b;line-height:1.55;"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<style>
@media(max-width:768px){
    .tm-svc-row { grid-template-columns:1fr !important; }
    .tm-svc-row > div { order:unset !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
