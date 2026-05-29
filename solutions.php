<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Solutions';
$pageDesc  = 'Tailored technology packages for African businesses at every stage of growth — starter, growth, and enterprise solutions.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="text-align:center;position:relative;z-index:2;">
        <div class="tm-label" style="justify-content:center;">Solutions</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.15;">Tailored Packages for<br>Every Stage of Growth</h1>
        <p style="font-size:1.1rem;color:#94a3b8;max-width:600px;margin:0 auto;line-height:1.7;">Whether you're just starting out or scaling across multiple locations, we have the right solution for where you are and where you want to go.</p>
    </div>
</section>

<!-- PACKAGES -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:28px;align-items:start;">
            <?php
            $packages = [
                [
                    'name'=>'Starter','price'=>'From $1,200','icon'=>'fa-solid fa-seedling','color'=>'#22c55e','popular'=>false,
                    'desc'=>'Perfect for small businesses ready to establish their digital presence and streamline core operations.',
                    'includes'=>['Professional business website','Basic CRM setup','WhatsApp Business integration','1 automation workflow','Google My Business setup','30-day support'],
                    'ideal'=>'Small businesses, sole traders, startups',
                ],
                [
                    'name'=>'Growth','price'=>'From $3,500','icon'=>'fa-solid fa-chart-line','color'=>'#f59e0b','popular'=>true,
                    'desc'=>'For growing businesses that need robust systems, an online presence, and automation to scale without adding headcount.',
                    'includes'=>['Custom business website or web app','Full CRM &amp; inventory system','E-commerce store','Up to 5 automation workflows','Digital marketing setup','Staff training &amp; onboarding','60-day support'],
                    'ideal'=>'SMEs, retail, service businesses',
                ],
                [
                    'name'=>'Enterprise','price'=>'Custom pricing','icon'=>'fa-solid fa-building-columns','color'=>'#a78bfa','popular'=>false,
                    'desc'=>'End-to-end digital transformation for established businesses with complex operations and multiple locations.',
                    'includes'=>['Custom ERP or enterprise platform','Full automation infrastructure','Multi-location management','Advanced analytics &amp; dashboards','Dedicated account manager','Ongoing development retainer','Priority support SLA'],
                    'ideal'=>'Multi-branch businesses, corporates',
                ],
            ];
            foreach($packages as $pkg): ?>
            <div class="tm-fade" style="background:#fff;border-radius:20px;overflow:hidden;border:<?= $pkg['popular'] ? '2px solid #f59e0b' : '1.5px solid #f1f5f9' ?>;position:relative;box-shadow:<?= $pkg['popular'] ? '0 20px 60px rgba(245,158,11,0.15)' : '0 4px 20px rgba(0,0,0,0.04)' ?>;">
                <?php if($pkg['popular']): ?>
                <div style="background:#f59e0b;color:#0f172a;font-size:0.72rem;font-weight:800;text-align:center;padding:7px;letter-spacing:.06em;">MOST POPULAR</div>
                <?php endif; ?>
                <div style="padding:32px;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <div style="width:48px;height:48px;border-radius:12px;background:<?= $pkg['popular'] ? 'rgba(245,158,11,0.1)' : 'rgba(34,197,94,0.08)' ?>;display:flex;align-items:center;justify-content:center;">
                            <i class="<?= $pkg['icon'] ?>" style="font-size:1.3rem;color:<?= $pkg['color'] ?>;"></i>
                        </div>
                        <div>
                            <div style="font-size:1.1rem;font-weight:900;color:#0f172a;"><?= $pkg['name'] ?></div>
                            <div style="font-size:0.8rem;color:#64748b;"><?= $pkg['ideal'] ?></div>
                        </div>
                    </div>
                    <div style="font-size:1.8rem;font-weight:900;color:#0f172a;margin-bottom:4px;"><?= $pkg['price'] ?></div>
                    <p style="font-size:0.875rem;color:#64748b;line-height:1.65;margin-bottom:24px;"><?= $pkg['desc'] ?></p>
                    <div style="margin-bottom:28px;">
                        <?php foreach($pkg['includes'] as $item): ?>
                        <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f8fafc;">
                            <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:0.78rem;flex-shrink:0;"></i>
                            <span style="font-size:0.875rem;color:#374151;"><?= $item ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= SITE_URL ?>/consultation.php?package=<?= urlencode($pkg['name']) ?>" class="<?= $pkg['popular'] ? 'tm-btn-primary' : 'tm-btn-green' ?>" style="width:100%;justify-content:center;display:flex;">
                        Get Started <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:48px;padding:28px;background:#fff;border-radius:16px;border:1.5px solid #f1f5f9;">
            <i class="fa-solid fa-circle-question" style="font-size:1.5rem;color:#16a34a;margin-bottom:12px;display:block;"></i>
            <h3 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Not sure which package is right for you?</h3>
            <p style="font-size:0.9rem;color:#64748b;margin-bottom:20px;">Book a free call. We'll assess your needs and recommend the right fit — no pressure.</p>
            <a href="<?= SITE_URL ?>/consultation.php" class="tm-btn-green">Book a Free Consultation</a>
        </div>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section style="padding:80px 0;background:#fff;">
    <div class="tm-container">
        <div style="text-align:center;max-width:600px;margin:0 auto 48px;">
            <div class="tm-label">Why Tedmark</div>
            <h2 class="tm-section-title">Why Businesses Choose Us</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;">
            <?php
            $whys = [
                ['icon'=>'fa-solid fa-map-location-dot','color'=>'#22c55e','title'=>'African Context','desc'=>'We understand local payment systems, infrastructure, and business culture — no guesswork.'],
                ['icon'=>'fa-solid fa-lock-open','color'=>'#60a5fa','title'=>'No Lock-in','desc'=>'You own everything we build. Full source code, full data. No vendor lock-in.'],
                ['icon'=>'fa-solid fa-gauge-high','color'=>'#f59e0b','title'=>'Fast Delivery','desc'=>'Most projects launched within 4–8 weeks. We move fast without cutting corners.'],
                ['icon'=>'fa-solid fa-headset','color'=>'#a78bfa','title'=>'Local Support','desc'=>'Dedicated support in your timezone. Real people who know your system.'],
            ];
            foreach($whys as $w): ?>
            <div class="tm-card tm-fade" style="text-align:center;">
                <div style="width:52px;height:52px;border-radius:14px;background:#f8fafc;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="<?= $w['icon'] ?>" style="font-size:1.3rem;color:<?= $w['color'] ?>;"></i>
                </div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:8px;"><?= $w['title'] ?></h3>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.6;"><?= $w['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
