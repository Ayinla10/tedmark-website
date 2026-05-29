<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Industries We Serve';
$pageDesc  = 'Tedmark Digital serves education, healthcare, retail, logistics, NGOs, SMEs and more across Africa with tailored technology solutions.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">Industries</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.15;">Built for the Sectors<br>Driving Africa Forward</h1>
        <p style="font-size:1.1rem;color:#94a3b8;max-width:620px;margin:0 auto;line-height:1.7;">We've worked across 8+ industries and understand the unique challenges, regulations, and workflows of each one.</p>
    </div>
</section>

<!-- INDUSTRIES GRID -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:28px;">
        <?php
        $industries = [
            ['icon'=>'fa-solid fa-graduation-cap','color'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)','title'=>'Education','subtitle'=>'Schools, Universities &amp; EdTech','desc'=>'From primary schools to universities, we build school management systems that handle admissions, results, parent communication, and fee collection.','projects'=>'25+'],
            ['icon'=>'fa-solid fa-hospital','color'=>'#f43f5e','bg'=>'rgba(244,63,94,0.1)','title'=>'Healthcare','subtitle'=>'Clinics, Hospitals &amp; Health Services','desc'=>'We digitize healthcare operations so providers can focus on patients. EHR, appointment booking, pharmacy management, and billing systems.','projects'=>'18+'],
            ['icon'=>'fa-solid fa-store','color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.1)','title'=>'Retail &amp; E-Commerce','subtitle'=>'Shops, Supermarkets &amp; Online Stores','desc'=>'Inventory, POS, and e-commerce systems that keep your retail business running smoothly — whether you have one store or twenty branches.','projects'=>'30+'],
            ['icon'=>'fa-solid fa-truck','color'=>'#60a5fa','bg'=>'rgba(96,165,250,0.1)','title'=>'Logistics &amp; Transport','subtitle'=>'Freight, Delivery &amp; Supply Chain','desc'=>'Logistics management platforms that automate dispatch, tracking, invoicing, and customer communication for transport businesses.','projects'=>'12+'],
            ['icon'=>'fa-solid fa-hand-holding-heart','color'=>'#a78bfa','bg'=>'rgba(167,139,250,0.1)','title'=>'NGOs &amp; Nonprofits','subtitle'=>'Foundations, Charities &amp; Development Orgs','desc'=>'We help nonprofits operate with the efficiency of a for-profit — donor management, program tracking, and impact reporting.','projects'=>'10+'],
            ['icon'=>'fa-solid fa-building','color'=>'#64748b','bg'=>'rgba(100,116,139,0.1)','title'=>'SMEs &amp; Startups','subtitle'=>'Growing Businesses &amp; New Ventures','desc'=>'We help African SMEs and startups build the digital foundation they need to compete, scale, and attract investors.','projects'=>'40+'],
            ['icon'=>'fa-solid fa-calendar-days','color'=>'#fb923c','bg'=>'rgba(251,146,60,0.1)','title'=>'Events &amp; Hospitality','subtitle'=>'Hotels, Restaurants &amp; Events Companies','desc'=>'Booking systems, event management platforms, and hospitality tools designed for the African market.','projects'=>'8+'],
            ['icon'=>'fa-solid fa-chart-pie','color'=>'#14b8a6','bg'=>'rgba(20,184,166,0.1)','title'=>'Finance &amp; Consulting','subtitle'=>'Accountants, Advisors &amp; Financial Firms','desc'=>'Client portals, reporting dashboards, and practice management systems for financial service firms.','projects'=>'15+'],
        ];
        foreach($industries as $ind): ?>
        <div class="tm-card tm-fade">
            <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:16px;">
                <div style="width:48px;height:48px;border-radius:12px;background:<?= $ind['bg'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="<?= $ind['icon'] ?>" style="font-size:1.2rem;color:<?= $ind['color'] ?>;"></i>
                </div>
                <div>
                    <h3 style="font-size:1.05rem;font-weight:800;color:#0f172a;line-height:1.2;"><?= $ind['title'] ?></h3>
                    <p style="font-size:0.78rem;color:#94a3b8;margin-top:2px;"><?= $ind['subtitle'] ?></p>
                </div>
            </div>
            <p style="font-size:0.875rem;color:#64748b;line-height:1.65;margin-bottom:16px;"><?= $ind['desc'] ?></p>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:14px;border-top:1px solid #f1f5f9;">
                <span style="font-size:0.78rem;color:#64748b;"><?= $ind['projects'] ?> projects completed</span>
                <a href="<?= SITE_URL ?>/consultation.php" style="font-size:0.8rem;font-weight:700;color:#16a34a;text-decoration:none;">Get started <i class="fa-solid fa-arrow-right fa-2xs"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
