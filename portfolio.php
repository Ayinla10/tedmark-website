<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'Portfolio';
$pageDesc    = 'View our portfolio of websites, business systems, e-commerce stores, and digital solutions built for growing businesses.';
$pageSeoPage = 'portfolio';
try { $projects = fetchAll("SELECT * FROM projects WHERE status='active' ORDER BY sort_order ASC"); } catch(Exception $e){ $projects=[]; }
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm2-page-hero">
    <div class="tm2-badge"><span></span> Our Work</div>
    <h1>Results We've Delivered</h1>
    <p>Real projects, real outcomes for real businesses across sectors and sizes.</p>
</section>

<!-- PORTFOLIO -->
<section class="tm2-section">
    <div class="tm2-container">
        <!-- Filter -->
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:40px;">
            <?php foreach(['all'=>'All Work','web'=>'Web','systems'=>'Systems','ecommerce'=>'E-Commerce','branding'=>'Branding','automation'=>'Automation'] as $k=>$v): ?>
            <button class="tm2-filter-btn<?= $k==='all'?' active':'' ?>" data-filter="<?= $k ?>"><?= $v ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Grid -->
        <div class="tm2-grid tm2-grid-2">
        <?php
        $fallback = [
            ['slug'=>'retailpro-website','cat'=>'web','icon'=>'fa-solid fa-globe','color'=>'#60a5fa','bg'=>'linear-gradient(135deg,#0f172a,#1e3a5f)','title'=>'RetailPro Website','client'=>'RetailPro GH','year'=>'2024','desc'=>'Modern e-commerce website with 340% increase in online sales within 3 months of launch.','tags'=>['Web','E-Commerce'],'result'=>'+340% online sales'],
            ['slug'=>'meditrack-erp','cat'=>'systems','icon'=>'fa-solid fa-database','color'=>'#22c55e','bg'=>'linear-gradient(135deg,#0f172a,#1a2e1a)','title'=>'MediTrack ERP','client'=>'MediTrack Clinics','year'=>'2024','desc'=>'Hospital management system now serving 3 clinic locations across Ghana with one unified platform.','tags'=>['Systems','Automation'],'result'=>'3 clinics unified'],
            ['slug'=>'foodflow-store','cat'=>'ecommerce','icon'=>'fa-solid fa-cart-shopping','color'=>'#fb923c','bg'=>'linear-gradient(135deg,#0f172a,#2d1a0f)','title'=>'FoodFlow Store','client'=>'FoodFlow','year'=>'2023','desc'=>'Online food ordering platform processing 500+ orders daily with real-time kitchen and delivery tracking.','tags'=>['E-Commerce','Systems'],'result'=>'500+ daily orders'],
            ['slug'=>'edulink-rebrand','cat'=>'branding','icon'=>'fa-solid fa-palette','color'=>'#a78bfa','bg'=>'linear-gradient(135deg,#0f172a,#1a1040)','title'=>'EduLink Rebrand','client'=>'EduLink Academy','year'=>'2024','desc'=>'Complete brand overhaul including logo, identity, and website resulting in 60% more student enrolments.','tags'=>['Branding','Web'],'result'=>'+60% enrolments'],
            ['slug'=>'logitrack-dashboard','cat'=>'systems','icon'=>'fa-solid fa-chart-bar','color'=>'#f59e0b','bg'=>'linear-gradient(135deg,#0f172a,#2d2200)','title'=>'LogiMove Dashboard','client'=>'LogiMove Logistics','year'=>'2023','desc'=>'Real-time fleet tracking and dispatch management dashboard with custom analytics and driver app.','tags'=>['Systems','Automation'],'result'=>'40% faster dispatch'],
            ['slug'=>'propestate-platform','cat'=>'web','icon'=>'fa-solid fa-building','color'=>'#f43f5e','bg'=>'linear-gradient(135deg,#0f172a,#2d0f1a)','title'=>'PropEstate Platform','client'=>'PropEstate Ghana','year'=>'2023','desc'=>'Property listing and management platform with 1,200+ active listings and agent portal.','tags'=>['Web','Systems'],'result'=>'1,200+ listings'],
            ['slug'=>'finserve-automation','cat'=>'automation','icon'=>'fa-solid fa-robot','color'=>'#14b8a6','bg'=>'linear-gradient(135deg,#0f172a,#0f2420)','title'=>'FinServe Automation','client'=>'FinServe Ltd','year'=>'2024','desc'=>'Full accounts payable automation reducing invoice processing time from 3 days to 2 hours.','tags'=>['Automation','Systems'],'result'=>'3 days → 2 hours'],
            ['slug'=>'freshharvest-brand','cat'=>'branding','icon'=>'fa-solid fa-star','color'=>'#22c55e','bg'=>'linear-gradient(135deg,#0f172a,#1a2e1a)','title'=>'FreshHarvest Brand','client'=>'FreshHarvest Foods','year'=>'2024','desc'=>'End-to-end brand identity for an agri-business startup entering the Ghanaian retail market.','tags'=>['Branding'],'result'=>'Market-ready brand'],
        ];
        $display = !empty($projects) ? $projects : $fallback;
        foreach($display as $proj):
            $isDb    = isset($proj['category']) && !isset($proj['cat']);
            $cat     = $isDb ? ($proj['category']??'') : $proj['cat'];
            $icon    = $isDb ? ($proj['icon']??'fa-solid fa-briefcase') : $proj['icon'];
            $color   = $isDb ? ($proj['color']??'#22c55e') : $proj['color'];
            $bg      = $isDb ? ($proj['bg']??'linear-gradient(135deg,#0f172a,#1e293b)') : $proj['bg'];
            $result  = $isDb ? ($proj['result']??'') : ($proj['result']??'');
            $client  = $isDb ? ($proj['client']??'') : ($proj['client']??'');
            $year    = $isDb ? ($proj['year']??'') : ($proj['year']??'');
            $desc    = $isDb ? ($proj['description']??'') : ($proj['desc']??'');
            $tagList = $isDb
                ? array_filter(array_map('trim', explode(',', $proj['tags']??$proj['category']??'')))
                : ($proj['tags']??[]);
            if(empty($tagList) && $cat) $tagList = [$cat];
        ?>
        <div class="tm2-card tm2-port-card" data-category="<?= htmlspecialchars($cat) ?>">
            <div class="tm2-port-media" style="<?= !empty($proj['cover_image']) ? 'background:url('.htmlspecialchars($proj['cover_image']).') center/cover no-repeat;' : 'background:radial-gradient(circle at 30% 30%,'.htmlspecialchars($color).'22,var(--bg-soft) 75%);' ?>">
                <?php if($result): ?>
                <span class="tm2-port-chip"><?= htmlspecialchars($result) ?></span>
                <?php endif; ?>
                <?php if(empty($proj['cover_image'])): ?>
                <i class="<?= htmlspecialchars($icon) ?>" style="font-size:2.3rem;color:<?= htmlspecialchars($color) ?>;opacity:0.85;"></i>
                <?php endif; ?>
            </div>
            <div class="tm2-port-body">
                <div style="display:flex;justify-content:space-between;align-items:start;">
                    <div class="tm2-port-meta"><?= htmlspecialchars(implode(' • ', array_map('strtoupper', $tagList))) ?></div>
                    <?php if($year): ?><span style="font-size:0.72rem;color:var(--muted);white-space:nowrap;margin-left:8px;flex-shrink:0;"><?= htmlspecialchars($year) ?></span><?php endif; ?>
                </div>
                <h3 style="margin-bottom:8px;"><?= htmlspecialchars($proj['title']) ?></h3>
                <p><?= htmlspecialchars($desc) ?><?= $client ? ' for '.htmlspecialchars($client) : '' ?>.</p>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="tm2-section" style="background:var(--bg-soft);">
    <div class="tm2-container tm2-stats">
        <?php foreach([['fa-solid fa-circle-check','80+','Completed Projects'],['fa-solid fa-building','8','Industries Served'],['fa-solid fa-face-smile','95%','Client Satisfaction'],['fa-solid fa-star','4.9★','Average Rating']] as $s): ?>
        <div>
            <div class="tm2-card-icon" style="margin:0 auto 10px;"><i class="<?= $s[0] ?>"></i></div>
            <div class="num accent"><?= $s[1] ?></div><div class="lbl"><?= $s[2] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
