<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Free Resources';
$pageDesc  = 'Free business technology resources, templates, guides, and tools to help businesses grow smarter.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">Free Resources</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.15;">Tools &amp; Resources to Help<br>Your Business Grow</h1>
        <p style="font-size:1.1rem;color:#94a3b8;max-width:560px;margin:0 auto;line-height:1.7;">Everything here is free. No email required, no catch, just practical tools for business owners.</p>
    </div>
</section>

<!-- INTERACTIVE TOOLS -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="text-align:center;max-width:600px;margin:0 auto 48px;">
            <div class="tm-label">Interactive Tools</div>
            <h2 class="tm-section-title">Free Business Tools</h2>
            <p class="tm-section-sub">Try these free tools to identify gaps and opportunities in your business.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:28px;margin-bottom:72px;">
            <?php
            $tools = [
                ['icon'=>'fa-solid fa-heart-pulse','color'=>'#f43f5e','bg'=>'rgba(244,63,94,0.08)','title'=>'Business Health Check','desc'=>'Get a full diagnostic of your business operations in under 5 minutes. Covers systems, processes, digital presence, and more.','link'=>'/tools/business-health.php','badge'=>'Most Popular','time'=>'5 min'],
                ['icon'=>'fa-solid fa-calculator','color'=>'#60a5fa','bg'=>'rgba(96,165,250,0.08)','title'=>'ROI Calculator','desc'=>'Calculate exactly how much time and money automation could save your business every year.','link'=>'/tools/roi-calculator.php','badge'=>'','time'=>'3 min'],
                ['icon'=>'fa-solid fa-wand-magic-sparkles','color'=>'#a78bfa','bg'=>'rgba(167,139,250,0.08)','title'=>'Service Recommender','desc'=>'Answer 5 quick questions and get a personalised digital transformation roadmap for your business.','link'=>'/tools/service-recommender.php','badge'=>'New','time'=>'2 min'],
            ];
            foreach($tools as $t): ?>
            <div class="tm-card tm-fade">
                <?php if($t['badge']): ?><span style="font-size:0.65rem;font-weight:800;background:#16a34a;color:#fff;padding:3px 10px;border-radius:20px;display:inline-block;margin-bottom:14px;"><?= $t['badge'] ?></span><?php endif; ?>
                <div style="width:52px;height:52px;border-radius:14px;background:<?= $t['bg'] ?>;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                    <i class="<?= $t['icon'] ?>" style="font-size:1.3rem;color:<?= $t['color'] ?>;"></i>
                </div>
                <h3 style="font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:8px;"><?= $t['title'] ?></h3>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.6;margin-bottom:18px;"><?= $t['desc'] ?></p>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:0.78rem;color:#94a3b8;"><i class="fa-solid fa-clock fa-2xs"></i> <?= $t['time'] ?></span>
                    <a href="<?= SITE_URL . $t['link'] ?>" class="tm-btn-green" style="padding:7px 16px;font-size:0.82rem;">Try Free <i class="fa-solid fa-arrow-right fa-2xs"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Templates/Guides -->
        <div style="text-align:center;max-width:600px;margin:0 auto 48px;">
            <div class="tm-label">Downloads</div>
            <h2 class="tm-section-title">Free Templates &amp; Guides</h2>
            <p class="tm-section-sub">Practical templates and guides used by businesses to organise and grow.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
            <?php
            $resources = [
                ['icon'=>'fa-solid fa-file-excel','color'=>'#22c55e','title'=>'Business Inventory Template','desc'=>'Excel spreadsheet for tracking stock levels, reorder points, and supplier info.','type'=>'Excel','badge'=>'Free'],
                ['icon'=>'fa-solid fa-file-pdf','color'=>'#f43f5e','title'=>'Digital Transformation Checklist','desc'=>'A step-by-step checklist for auditing and planning your business\'s digital journey.','type'=>'PDF','badge'=>'Free'],
                ['icon'=>'fa-solid fa-file-word','color'=>'#60a5fa','title'=>'Client Proposal Template','desc'=>'A professional proposal template for service businesses to win more clients.','type'=>'Word','badge'=>'Free'],
                ['icon'=>'fa-solid fa-file-excel','color'=>'#f59e0b','title'=>'Cash Flow Forecast Template','desc'=>'Monthly cash flow projection spreadsheet for small and medium businesses.','type'=>'Excel','badge'=>'Free'],
                ['icon'=>'fa-solid fa-file-pdf','color'=>'#a78bfa','title'=>'Social Media Content Calendar','desc'=>'A 30-day content calendar template to plan and manage your social media posts.','type'=>'PDF','badge'=>'Free'],
                ['icon'=>'fa-solid fa-file-pdf','color'=>'#14b8a6','title'=>'Website Brief Template','desc'=>'Tell us exactly what you need from a new website using this structured brief template.','type'=>'PDF','badge'=>'Free'],
            ];
            foreach($resources as $r): ?>
            <div class="tm-card tm-fade" style="display:flex;gap:14px;align-items:flex-start;">
                <div style="width:44px;height:44px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="<?= $r['icon'] ?>" style="color:<?= $r['color'] ?>;font-size:1.2rem;"></i>
                </div>
                <div style="flex:1;">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px;">
                        <h3 style="font-size:0.9rem;font-weight:600;color:#0f172a;line-height:1.3;"><?= $r['title'] ?></h3>
                        <span style="font-size:0.65rem;font-weight:600;background:#f0fdf4;color:#16a34a;padding:2px 8px;border-radius:10px;white-space:nowrap;margin-left:8px;"><?= $r['badge'] ?></span>
                    </div>
                    <p style="font-size:0.8rem;color:#64748b;line-height:1.5;margin-bottom:10px;"><?= $r['desc'] ?></p>
                    <a href="<?= SITE_URL ?>/consultation.php" style="font-size:0.78rem;font-weight:600;color:#16a34a;text-decoration:none;">
                        <i class="fa-solid fa-download fa-2xs"></i> Download <?= $r['type'] ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
