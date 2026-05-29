<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Portfolio';
$pageDesc  = 'View our portfolio of websites, business systems, e-commerce stores, and digital solutions built for African businesses.';
try { $projects = fetchAll("SELECT * FROM projects WHERE status='active' ORDER BY sort_order ASC"); } catch(Exception $e){ $projects=[]; }
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">Our Work</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.15;">Results We've Delivered</h1>
        <p style="font-size:1.1rem;color:#94a3b8;max-width:560px;margin:0 auto;line-height:1.7;">Real projects, real outcomes for real African businesses across sectors and sizes.</p>
    </div>
</section>

<!-- PORTFOLIO -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <!-- Filter -->
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:48px;">
            <?php foreach(['all'=>'All Work','web'=>'Web','systems'=>'Systems','ecommerce'=>'E-Commerce','branding'=>'Branding','automation'=>'Automation'] as $k=>$v): ?>
            <button class="tm-filter-btn<?= $k==='all'?' active':'' ?>" data-filter="<?= $k ?>"><?= $v ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:28px;">
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
            $isDynamic = isset($proj['category']);
        ?>
        <div class="tm-port-card tm-fade" data-category="<?= $isDynamic ? htmlspecialchars($proj['category']) : $proj['cat'] ?>" style="padding:0;">
            <div style="height:200px;background:<?= $isDynamic ? '#1e293b' : $proj['bg'] ?>;display:flex;align-items:center;justify-content:center;position:relative;">
                <i class="<?= $isDynamic ? 'fa-solid fa-briefcase' : $proj['icon'] ?>" style="font-size:3rem;color:<?= $isDynamic ? '#22c55e' : $proj['color'] ?>;opacity:0.5;"></i>
                <?php if(!$isDynamic && isset($proj['result'])): ?>
                <div style="position:absolute;bottom:12px;left:12px;background:rgba(0,0,0,0.6);color:#22c55e;font-size:0.72rem;font-weight:700;padding:5px 10px;border-radius:6px;backdrop-filter:blur(4px);">
                    <i class="fa-solid fa-arrow-trend-up fa-xs"></i> <?= $proj['result'] ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="padding:20px 24px 24px;">
                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                    <h3 style="font-size:1rem;font-weight:800;color:#0f172a;line-height:1.3;"><?= htmlspecialchars($isDynamic ? $proj['title'] : $proj['title']) ?></h3>
                    <?php if(!$isDynamic): ?>
                    <span style="font-size:0.72rem;color:#94a3b8;white-space:nowrap;margin-left:8px;"><?= $proj['year'] ?></span>
                    <?php endif; ?>
                </div>
                <?php if(!$isDynamic): ?><p style="font-size:0.75rem;color:#16a34a;font-weight:600;margin-bottom:8px;"><?= $proj['client'] ?></p><?php endif; ?>
                <p style="font-size:0.85rem;color:#64748b;line-height:1.6;margin-bottom:14px;"><?= htmlspecialchars($isDynamic ? ($proj['description']??'') : $proj['desc']) ?></p>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php $tags = $isDynamic ? [$proj['category']] : $proj['tags']; foreach($tags as $tag): ?>
                    <span class="tm-port-tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="tm-stats-band">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:32px;text-align:center;">
            <?php foreach([['80+','Completed Projects'],['8','Industries Served'],['95%','Client Satisfaction'],['4.9★','Average Rating']] as $s): ?>
            <div><div style="font-size:2.2rem;font-weight:900;color:#fff;"><?= $s[0] ?></div><div style="font-size:0.85rem;color:rgba(255,255,255,0.65);margin-top:6px;"><?= $s[1] ?></div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
