<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'Industries We Serve';
$pageDesc    = 'Tedmark Digital serves education, healthcare, retail, logistics, NGOs, SMEs and more with tailored technology solutions.';
$pageSeoPage = 'industries';
try { $dbIndustries = fetchAll("SELECT * FROM industries WHERE status='active' ORDER BY sort_order ASC"); }
catch(Exception $e) { $dbIndustries = []; }
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm2-page-hero">
    <div class="tm2-badge"><span></span> Industries</div>
    <h1>Built for the Sectors Driving Growth Forward</h1>
    <p>We've worked across 8+ industries and understand the unique challenges, regulations, and workflows of each one.</p>
</section>

<!-- INDUSTRIES GRID -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-grid tm2-grid-3">
        <?php
        $indFallback = [
            ['icon'=>'fa-solid fa-graduation-cap','color'=>'#22c55e','title'=>'Education','description'=>'From primary schools to universities, we build school management systems that handle admissions, results, parent communication, and fee collection.'],
            ['icon'=>'fa-solid fa-hospital','color'=>'#f43f5e','title'=>'Healthcare','description'=>'We digitize healthcare operations so providers can focus on patients. EHR, appointment booking, pharmacy management, and billing systems.'],
            ['icon'=>'fa-solid fa-store','color'=>'#f59e0b','title'=>'Retail & E-Commerce','description'=>'Inventory, POS, and e-commerce systems that keep your retail business running smoothly, whether you have one store or twenty branches.'],
            ['icon'=>'fa-solid fa-truck','color'=>'#60a5fa','title'=>'Logistics & Transport','description'=>'Logistics management platforms that automate dispatch, tracking, invoicing, and customer communication for transport businesses.'],
            ['icon'=>'fa-solid fa-hand-holding-heart','color'=>'#a78bfa','title'=>'NGOs & Nonprofits','description'=>'We help nonprofits operate with the efficiency of a for-profit, covering donor management, program tracking, and impact reporting.'],
            ['icon'=>'fa-solid fa-building','color'=>'#64748b','title'=>'SMEs & Startups','description'=>'We help SMEs and startups build the digital foundation they need to compete, scale, and attract investors.'],
            ['icon'=>'fa-solid fa-calendar-days','color'=>'#fb923c','title'=>'Events & Hospitality','description'=>'Booking systems, event management platforms, and hospitality tools designed to scale with you.'],
            ['icon'=>'fa-solid fa-chart-pie','color'=>'#14b8a6','title'=>'Finance & Consulting','description'=>'Client portals, reporting dashboards, and practice management systems for financial service firms.'],
        ];
        $industries = !empty($dbIndustries) ? $dbIndustries : $indFallback;
        foreach($industries as $ind):
            $indColor = $ind['color'] ?? '#22c55e';
            $indBg    = 'rgba('.implode(',', array_map('hexdec', str_split(ltrim($indColor,'#'),2))).', 0.1)';
        ?>
        <div class="tm2-card">
            <div class="tm2-card-icon"><i class="<?= htmlspecialchars($ind['icon']??'fa-solid fa-building') ?>"></i></div>
            <h3><?= htmlspecialchars($ind['title']) ?></h3>
            <p style="margin-bottom:16px;"><?= htmlspecialchars($ind['description']??'') ?></p>
            <div style="display:flex;align-items:center;justify-content:flex-end;padding-top:14px;border-top:1px solid var(--border);">
                <a href="<?= SITE_URL ?>/consultation" style="font-size:0.8rem;font-weight:600;color:var(--accent);text-decoration:none;">Get started <i class="fa-solid fa-arrow-right fa-2xs"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
