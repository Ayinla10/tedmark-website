<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle      = 'Helping African Businesses Run Smarter With Technology';
$pageHasDarkHero = true;
$pageDesc        = 'Tedmark Digital Agency helps African businesses run smarter with technology, automation, business systems, and modern digital infrastructure.';
$pageSeoPage     = 'home';  // tells SEO engine to load seo_pages WHERE page_key='home'

// Load settings from DB
try {
    $settingsRows = fetchAll("SELECT `key`, `value` FROM settings");
    $cfg = array_column($settingsRows, 'value', 'key');
} catch(Exception $e) { $cfg = []; }

function cfg($cfg, $key, $default='') { return htmlspecialchars($cfg[$key] ?? $default); }

try { $recentPosts = fetchAll("SELECT * FROM posts WHERE status='published' ORDER BY published_at DESC LIMIT 3"); } catch(Exception $e){ $recentPosts=[]; }
try { $testimonials = fetchAll("SELECT * FROM testimonials WHERE status='active' ORDER BY sort_order ASC LIMIT 3"); } catch(Exception $e){ $testimonials=[]; }
try { $projects     = fetchAll("SELECT * FROM projects WHERE status='active' ORDER BY sort_order ASC LIMIT 6"); } catch(Exception $e){ $projects=[]; }

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<?php
$heroBg    = $cfg['hero_bg_image'] ?? 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&q=80&auto=format&fit=crop';
$heroOp    = $cfg['hero_overlay_opacity'] ?? '0.92';
$heroStyle = "background: linear-gradient(135deg, rgba(6,11,24,{$heroOp}) 0%, rgba(10,22,40,{$heroOp}) 50%, rgba(13,31,60,{$heroOp}) 100%), url('" . htmlspecialchars($heroBg) . "') center/cover no-repeat;";
?>
<section class="tm-hero" style="<?= $heroStyle ?>">
    <div class="tm-grid-lines"></div>
    <div class="tm-container" style="position:relative;z-index:2;padding-top:140px;padding-bottom:80px;">
        <div class="tm-hero-grid">

            <!-- Left copy -->
            <div class="tm-fade">
                <div class="tm-trust-badge">
                    <span style="width:8px;height:8px;background:#4ade80;border-radius:50%;display:inline-block;animation:pulse 2s infinite;"></span>
                    <?= cfg($cfg,'hero_badge','Helping African Businesses Run Smarter') ?>
                </div>
                <h1 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(2.4rem,4.8vw,3.6rem);font-weight:900;line-height:1.1;color:#fff;margin-bottom:24px;margin-top:20px;letter-spacing:-0.02em;">
                    <?= cfg($cfg,'hero_h1_line1','We Build Systems.') ?><br>
                    <?= cfg($cfg,'hero_h1_line2','We Automate Work.') ?><br>
                    <span class="tm-text-gradient" style="white-space:nowrap;"><?= cfg($cfg,'hero_h1_line3','We Grow Businesses.') ?></span>
                </h1>
                <p style="font-size:1.05rem;color:#cbd5e1;line-height:1.8;max-width:500px;margin-bottom:40px;">
                    <?= cfg($cfg,'hero_subtext','We help businesses organize, automate, and digitize their operations using smart systems and modern technology so they can save time, reduce costs, and grow without limits.') ?>
                </p>
                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <a href="<?= SITE_URL ?>/consultation.php" class="tm-btn-primary">
                        <?= cfg($cfg,'hero_btn_primary','Book a Free Strategy Session') ?> <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </a>
                    <a href="<?= SITE_URL ?>/solutions.php" class="tm-btn-secondary">
                        <i class="fa-solid fa-circle-play fa-sm"></i> <?= cfg($cfg,'hero_btn_secondary','Explore Our Solutions') ?>
                    </a>
                </div>
                <div style="display:flex;gap:20px;margin-top:48px;flex-wrap:wrap;align-items:center;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-building" style="color:#4ade80;font-size:1.1rem;"></i>
                        <div><div style="font-size:1.5rem;font-weight:800;color:#fff;line-height:1;"><?= cfg($cfg,'stat_1_value','80+') ?></div><div style="font-size:0.75rem;color:#94a3b8;margin-top:2px;"><?= cfg($cfg,'stat_1_label','Projects Delivered') ?></div></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-rocket" style="color:#4ade80;font-size:1.1rem;"></i>
                        <div><div style="font-size:1.5rem;font-weight:800;color:#fff;line-height:1;"><?= cfg($cfg,'stat_2_value','95%') ?></div><div style="font-size:0.75rem;color:#94a3b8;margin-top:2px;"><?= cfg($cfg,'stat_2_label','Client Satisfaction') ?></div></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-star" style="color:#4ade80;font-size:1.1rem;"></i>
                        <div><div style="font-size:1.5rem;font-weight:800;color:#fff;line-height:1;"><?= cfg($cfg,'stat_3_value','8') ?></div><div style="font-size:0.75rem;color:#94a3b8;margin-top:2px;"><?= cfg($cfg,'stat_3_label','Industries Served') ?></div></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-headset" style="color:#4ade80;font-size:1.1rem;"></i>
                        <div><div style="font-size:1.5rem;font-weight:800;color:#fff;line-height:1;"><?= cfg($cfg,'stat_4_value','3yrs') ?></div><div style="font-size:0.75rem;color:#94a3b8;margin-top:2px;"><?= cfg($cfg,'stat_4_label','In Business') ?></div></div>
                    </div>
                </div>
            </div>

            <!-- Right: Dashboard mockup (white / light) -->
            <div class="tm-hero-visual" style="position:relative;animation:tm-float 6s ease-in-out infinite;">
                <!-- Outer glow ring -->
                <div style="position:absolute;inset:-12px;border-radius:28px;background:radial-gradient(ellipse at 60% 40%,rgba(74,222,128,0.18),transparent 70%);pointer-events:none;"></div>

                <div style="background:#ffffff;border-radius:22px;padding:24px;box-shadow:0 32px 80px rgba(0,0,0,0.35),0 0 0 1px rgba(255,255,255,0.12);">
                    <!-- Header bar -->
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:28px;height:28px;background:#dcfce7;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fa-solid fa-chart-line" style="font-size:12px;color:#16a34a;"></i>
                            </div>
                            <div style="font-size:0.82rem;font-weight:700;color:#0f172a;">Business Dashboard</div>
                        </div>
                        <div style="display:flex;gap:5px;">
                            <span style="width:9px;height:9px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                            <span style="width:9px;height:9px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                            <span style="width:9px;height:9px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                        </div>
                    </div>

                    <!-- Metric cards -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                        <div style="background:#f8fafc;border-radius:12px;padding:14px;border:1px solid #e2e8f0;">
                            <div style="font-size:0.68rem;color:#64748b;margin-bottom:5px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Monthly Revenue</div>
                            <div style="font-size:1.35rem;font-weight:800;color:#15803d;">GH₵ 48,200</div>
                            <div style="font-size:0.68rem;color:#16a34a;margin-top:4px;font-weight:600;"><i class="fa-solid fa-arrow-up fa-2xs"></i> +23% this month</div>
                        </div>
                        <div style="background:#f8fafc;border-radius:12px;padding:14px;border:1px solid #e2e8f0;">
                            <div style="font-size:0.68rem;color:#64748b;margin-bottom:5px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Active Clients</div>
                            <div style="font-size:1.35rem;font-weight:800;color:#0f172a;">142</div>
                            <div style="font-size:0.68rem;color:#16a34a;margin-top:4px;font-weight:600;"><i class="fa-solid fa-arrow-up fa-2xs"></i> +8 new this week</div>
                        </div>
                    </div>

                    <!-- Chart bars -->
                    <div style="background:#f8fafc;border-radius:12px;padding:16px;border:1px solid #e2e8f0;margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <div style="font-size:0.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Weekly Performance</div>
                            <div style="font-size:0.65rem;font-weight:700;color:#16a34a;background:#dcfce7;padding:2px 8px;border-radius:99px;">↑ 18%</div>
                        </div>
                        <div style="display:flex;gap:7px;align-items:flex-end;height:52px;">
                            <?php foreach([38,58,44,82,62,92,74] as $h): ?>
                            <div style="flex:1;background:linear-gradient(180deg,#4ade80,#16a34a);border-radius:4px 4px 0 0;height:<?= $h ?>%;"></div>
                            <?php endforeach; ?>
                        </div>
                        <div style="display:flex;gap:7px;margin-top:6px;">
                            <?php foreach(['M','T','W','T','F','S','S'] as $d): ?>
                            <div style="flex:1;text-align:center;font-size:0.6rem;color:#94a3b8;font-weight:600;"><?= $d ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Live automation row -->
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;">
                        <div style="width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0;box-shadow:0 0 0 3px rgba(34,197,94,0.25);animation:pulse 2s infinite;"></div>
                        <div style="font-size:0.76rem;color:#166534;flex:1;font-weight:500;">Automation running: Invoice dispatch</div>
                        <div style="font-size:0.68rem;color:#16a34a;font-weight:700;background:#dcfce7;padding:2px 8px;border-radius:99px;">Live</div>
                    </div>
                </div>

                <!-- Floating badge -->
                <div style="position:absolute;bottom:-18px;left:-18px;background:#f59e0b;color:#000;border-radius:12px;padding:10px 16px;font-size:0.78rem;font-weight:700;box-shadow:0 8px 24px rgba(245,158,11,0.45);animation:tm-float2 4s ease-in-out infinite;white-space:nowrap;">
                    <i class="fa-solid fa-bolt"></i> 3x faster operations
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CLIENT LOGOS ===== -->
<section style="background:#060d1a;padding:40px 0;border-bottom:1px solid #0f172a;">
    <div class="tm-container">
        <p style="text-align:center;font-size:0.78rem;color:#94a3b8;font-weight:600;letter-spacing:.12em;text-transform:uppercase;margin-bottom:28px;">Trusted by businesses across Africa</p>
        <div style="display:flex;justify-content:center;align-items:center;gap:40px;flex-wrap:wrap;">
            <?php
            $logos = [
                ['icon'=>'fa-solid fa-store','name'=>'RetailPro GH'],
                ['icon'=>'fa-solid fa-hospital','name'=>'MediTrack'],
                ['icon'=>'fa-solid fa-graduation-cap','name'=>'EduLink'],
                ['icon'=>'fa-solid fa-utensils','name'=>'FoodFlow'],
                ['icon'=>'fa-solid fa-building','name'=>'PropEstate'],
                ['icon'=>'fa-solid fa-truck','name'=>'LogiMove'],
            ];
            foreach($logos as $l): ?>
            <div style="display:flex;align-items:center;gap:8px;">
                <i class="<?= $l['icon'] ?>" style="font-size:1.1rem;color:#4ade80;"></i>
                <span style="font-size:0.85rem;font-weight:700;color:#cbd5e1;letter-spacing:.04em;"><?= $l['name'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== PROBLEMS ===== -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="text-align:center;max-width:640px;margin:0 auto 60px;">
            <div class="tm-label">The Problem</div>
            <h2 class="tm-section-title">Sound Familiar?</h2>
            <p class="tm-section-sub">Most African businesses are held back by the same operational bottlenecks. We fix all of them.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;">
            <?php
            $problems = [
                ['icon'=>'fa-solid fa-gears','color'=>'#ef4444','bg'=>'#fef2f2','title'=>'Manual Processes','desc'=>'Hours lost to repetitive tasks that could be automated — data entry, invoicing, reporting.'],
                ['icon'=>'fa-solid fa-layer-group','color'=>'#f59e0b','bg'=>'#fffbeb','title'=>'Scattered Information','desc'=>'Customer data, finances, and operations spread across spreadsheets and paper files.'],
                ['icon'=>'fa-solid fa-comment-slash','color'=>'#8b5cf6','bg'=>'#f5f3ff','title'=>'Poor Communication','desc'=>'Team silos, missed follow-ups, and inconsistent customer experiences costing you sales.'],
                ['icon'=>'fa-solid fa-chart-line','color'=>'#3b82f6','bg'=>'#eff6ff','title'=>'Lack of Visibility','desc'=>'No real-time dashboards or reports — you\'re making decisions without accurate data.'],
            ];
            foreach($problems as $p): ?>
            <div class="tm-card tm-fade" style="text-align:center;">
                <div style="width:56px;height:56px;border-radius:14px;background:<?= $p['bg'] ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="<?= $p['icon'] ?>" style="font-size:1.4rem;color:<?= $p['color'] ?>;"></i>
                </div>
                <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:8px;"><?= $p['title'] ?></h3>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.6;"><?= $p['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== SERVICES ===== -->
<section style="padding:96px 0;background:#fff;">
    <div class="tm-container">
        <div style="text-align:center;max-width:640px;margin:0 auto 60px;">
            <div class="tm-label">What We Do</div>
            <h2 class="tm-section-title">Everything Your Business Needs to Thrive Digitally</h2>
            <p class="tm-section-sub">End-to-end digital transformation — from strategy to implementation to ongoing support.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:28px;">
            <?php
            $services = [
                ['icon'=>'fa-solid fa-gears','color'=>'#22c55e','bg'=>'rgba(34,197,94,0.1)','title'=>'Business Systems','desc'=>'Custom ERPs, inventory management, HR systems, and operational platforms tailored for your business.','link'=>'/services.php'],
                ['icon'=>'fa-solid fa-robot','color'=>'#a78bfa','bg'=>'rgba(139,92,246,0.1)','title'=>'Automation','desc'=>'Automate invoicing, reporting, communications, and workflows to save 10+ hours per week.','link'=>'/services.php'],
                ['icon'=>'fa-solid fa-code','color'=>'#60a5fa','bg'=>'rgba(59,130,246,0.1)','title'=>'Web Development','desc'=>'Fast, modern websites and web applications that convert visitors into paying customers.','link'=>'/services.php'],
                ['icon'=>'fa-solid fa-cart-shopping','color'=>'#fb923c','bg'=>'rgba(251,146,60,0.1)','title'=>'E-Commerce','desc'=>'Online stores with payments, inventory, and shipping — ready to sell across Africa and beyond.','link'=>'/services.php'],
            ];
            foreach($services as $s): ?>
            <div class="tm-card tm-fade" style="border-top:3px solid <?= $s['color'] ?>;">
                <div class="tm-svc-icon" style="background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>;margin-bottom:20px;">
                    <i class="<?= $s['icon'] ?>"></i>
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;color:#0f172a;margin-bottom:10px;"><?= $s['title'] ?></h3>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.6;margin-bottom:20px;"><?= $s['desc'] ?></p>
                <a href="<?= SITE_URL . $s['link'] ?>" style="font-size:0.85rem;font-weight:600;color:#16a34a;display:inline-flex;align-items:center;gap:6px;">
                    Learn more <i class="fa-solid fa-arrow-right fa-2xs"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:48px;">
            <a href="<?= SITE_URL ?>/services.php" class="tm-btn-green">
                View All Services <i class="fa-solid fa-arrow-right fa-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== TOOLS BAND ===== -->
<section style="background:#f8fafc;padding:56px 0;">
    <div class="tm-container">
        <!-- Contained card with 3D perspective -->
        <div style="max-width:100%;margin:0 auto;background:linear-gradient(135deg,#0d1f3c 0%,#091428 100%);border:1px solid rgba(255,255,255,0.07);border-radius:24px;padding:48px;box-shadow:0 32px 64px rgba(0,0,0,0.5),0 0 0 1px rgba(255,255,255,0.04),inset 0 1px 0 rgba(255,255,255,0.06);transform-style:preserve-3d;perspective:1000px;position:relative;overflow:hidden;">
            <!-- subtle glow -->
            <div style="position:absolute;top:-60px;right:-60px;width:300px;height:300px;background:radial-gradient(circle,rgba(34,197,94,0.08) 0%,transparent 70%);pointer-events:none;"></div>
            <!-- grid lines -->
            <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;border-radius:24px;"></div>

            <div style="display:grid;grid-template-columns:260px 1fr;gap:48px;align-items:center;position:relative;z-index:1;" class="tm-tools-band-grid">

                <!-- Left: heading -->
                <div class="tm-fade">
                    <div style="display:inline-flex;align-items:center;gap:6px;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#f59e0b;margin-bottom:16px;white-space:nowrap;">
                        <span style="width:5px;height:5px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                        Powerful Tools for Smarter Decisions
                    </div>
                    <h2 style="font-size:1.6rem;font-weight:900;color:#fff;line-height:1.2;margin-bottom:12px;">Try Our Free Business Tools</h2>
                    <p style="font-size:0.875rem;color:#94a3b8;line-height:1.7;">Get insights about your business and discover opportunities for growth.</p>
                </div>

                <!-- Right: tool cards row -->
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;" class="tm-tools-cards-grid">
                    <?php
                    $tools = [
                        ['icon'=>'fa-solid fa-heart-pulse',       'title'=>'Business Health Checker','desc'=>'Answer a few questions and get a personalized report on your business health.',  'cta'=>'Try Now',              'link'=>'/tools/business-health.php'],
                        ['icon'=>'fa-solid fa-calculator',         'title'=>'ROI Calculator',          'desc'=>'Calculate how much time and money your business can save with automation.',      'cta'=>'Calculate Now',        'link'=>'/tools/roi-calculator.php'],
                        ['icon'=>'fa-solid fa-wand-magic-sparkles','title'=>'Solution Recommender',    'desc'=>'Tell us about your business and we\'ll recommend the right solutions for you.','cta'=>'Get Recommendations',  'link'=>'/tools/service-recommender.php'],
                    ];
                    foreach($tools as $t): ?>
                    <div class="tm-fade tm-tool-card" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:22px;transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease,background .3s ease;transform-style:preserve-3d;cursor:pointer;"
                         onmouseover="this.style.transform='perspective(600px) rotateX(4deg) rotateY(-3deg) translateY(-6px)';this.style.boxShadow='0 20px 40px rgba(0,0,0,0.4),0 0 20px rgba(34,197,94,0.1)';this.style.borderColor='rgba(34,197,94,0.35)';this.style.background='rgba(255,255,255,0.07)'"
                         onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='rgba(255,255,255,0.08)';this.style.background='rgba(255,255,255,0.04)'">
                        <div style="width:42px;height:42px;border-radius:10px;background:rgba(34,197,94,0.12);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                            <i class="<?= $t['icon'] ?>" style="font-size:1.1rem;color:#22c55e;"></i>
                        </div>
                        <h3 style="font-size:0.9rem;font-weight:700;color:#fff;margin-bottom:8px;line-height:1.3;"><?= $t['title'] ?></h3>
                        <p style="font-size:0.8rem;color:#94a3b8;line-height:1.6;margin-bottom:16px;"><?= $t['desc'] ?></p>
                        <a href="<?= SITE_URL . $t['link'] ?>" style="font-size:0.8rem;font-weight:700;color:#22c55e;display:inline-flex;align-items:center;gap:5px;text-decoration:none;">
                            <?= $t['cta'] ?> <i class="fa-solid fa-arrow-right fa-2xs"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>
</section>
<style>
@media(max-width:900px){ .tm-tools-band-grid{grid-template-columns:1fr !important} }
@media(max-width:640px){ .tm-tools-cards-grid{grid-template-columns:1fr !important} }
</style>

<!-- ===== PORTFOLIO ===== -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="text-align:center;max-width:640px;margin:0 auto 48px;">
            <div class="tm-label">Our Work</div>
            <h2 class="tm-section-title">Results We've Delivered</h2>
            <p class="tm-section-sub">Real projects, real outcomes for real African businesses.</p>
        </div>
        <!-- Filter -->
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:40px;">
            <?php foreach(['all'=>'All Work','web'=>'Web','systems'=>'Systems','ecommerce'=>'E-Commerce','branding'=>'Branding'] as $k=>$v): ?>
            <button class="tm-filter-btn<?= $k==='all'?' active':'' ?>" data-filter="<?= $k ?>"><?= $v ?></button>
            <?php endforeach; ?>
        </div>
        <!-- Grid -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;">
            <?php
            $fallback = [
                ['cat'=>'web',      'icon'=>'fa-solid fa-globe',        'color'=>'#60a5fa','bg'=>'linear-gradient(135deg,#0f172a,#1e3a5f)','title'=>'RetailPro Website','desc'=>'Modern e-commerce site with 340% increase in online sales.',        'tags'=>['Web','E-Commerce']],
                ['cat'=>'systems',  'icon'=>'fa-solid fa-database',     'color'=>'#22c55e','bg'=>'linear-gradient(135deg,#0f172a,#1a2e1a)','title'=>'MediTrack ERP',    'desc'=>'Hospital management system serving 3 facilities across Ghana.',   'tags'=>['Systems','Automation']],
                ['cat'=>'ecommerce','icon'=>'fa-solid fa-cart-shopping', 'color'=>'#fb923c','bg'=>'linear-gradient(135deg,#0f172a,#2d1a0f)','title'=>'FoodFlow Store',   'desc'=>'Online ordering platform processing 500+ orders daily.',           'tags'=>['E-Commerce']],
            ];
            $display = !empty($projects) ? $projects : $fallback;
            foreach($display as $proj):
                $isDynamic = isset($proj['category']);
            ?>
            <div class="tm-port-card tm-fade" data-category="<?= $isDynamic ? htmlspecialchars($proj['category']) : $proj['cat'] ?>" style="padding:0;">
                <div style="height:180px;background:<?= $isDynamic ? '#1e293b' : $proj['bg'] ?>;display:flex;align-items:center;justify-content:center;">
                    <i class="<?= $isDynamic ? 'fa-solid fa-briefcase' : $proj['icon'] ?>" style="font-size:2.5rem;color:<?= $isDynamic ? '#22c55e' : $proj['color'] ?>;opacity:0.6;"></i>
                </div>
                <div style="padding:20px 24px 24px;">
                    <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:8px;"><?= htmlspecialchars($isDynamic ? $proj['title'] : $proj['title']) ?></h3>
                    <p style="font-size:0.85rem;color:#64748b;margin-bottom:14px;"><?= htmlspecialchars($isDynamic ? ($proj['description']??'') : $proj['desc']) ?></p>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <?php $tags = $isDynamic ? [$proj['category']] : $proj['tags']; foreach($tags as $tag): ?>
                        <span class="tm-port-tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:48px;">
            <a href="<?= SITE_URL ?>/portfolio.php" class="tm-btn-green">View Full Portfolio <i class="fa-solid fa-arrow-right fa-xs"></i></a>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section style="padding:96px 0;background:#fff;">
    <div class="tm-container">
        <div style="text-align:center;max-width:600px;margin:0 auto 60px;">
            <div class="tm-label">The Process</div>
            <h2 class="tm-section-title">How We Work With You</h2>
            <p class="tm-section-sub">From first call to launch and beyond — a simple, proven process.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:32px;">
            <?php
            $steps = [
                ['num'=>'01','title'=>'Discovery Call','desc'=>'We learn about your business, goals, challenges, and current systems in a free 30-minute consultation.'],
                ['num'=>'02','title'=>'Digital Roadmap','desc'=>'We create a custom plan showing exactly what to build, the timeline, and expected outcomes.'],
                ['num'=>'03','title'=>'Build & Launch','desc'=>'Our team builds your solution with weekly updates and your full involvement throughout.'],
                ['num'=>'04','title'=>'Grow & Scale','desc'=>'Ongoing support, training, and optimisation to ensure you keep getting better results over time.'],
            ];
            foreach($steps as $s): ?>
            <div class="tm-fade" style="text-align:center;">
                <div class="tm-step-num"><?= $s['num'] ?></div>
                <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:16px 0 8px;"><?= $s['title'] ?></h3>
                <p style="font-size:0.875rem;color:#64748b;line-height:1.6;"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section style="padding:96px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="text-align:center;max-width:640px;margin:0 auto 60px;">
            <div class="tm-label">Client Stories</div>
            <h2 class="tm-section-title">What Our Clients Say</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:28px;">
            <?php
            $fallbackTest = [
                ['name'=>'Kofi Asante','role'=>'CEO, RetailPro Ghana','rating'=>5,'text'=>'Tedmark transformed our business completely. The new inventory system alone saves us 15 hours a week and we have zero stockouts now.'],
                ['name'=>'Ama Owusu','role'=>'Director, MediTrack Clinics','rating'=>5,'text'=>'The ERP system they built handles our 3 clinic locations flawlessly. Patient records, billing, staff management — all in one place.'],
                ['name'=>'David Mensah','role'=>'Founder, FoodFlow','rating'=>5,'text'=>'From idea to a fully operational online store in 6 weeks. We went from 50 orders/day to over 500. Tedmark is the real deal.'],
            ];
            $display = !empty($testimonials) ? $testimonials : $fallbackTest;
            foreach($display as $t):
                $isDynamic = isset($t['client_name']);
                $name = $isDynamic ? $t['client_name'] : $t['name'];
                $role = $isDynamic ? $t['client_role'] : $t['role'];
                $text = $isDynamic ? $t['content'] : $t['text'];
                $rating = (int)($t['rating'] ?? 5);
            ?>
            <div class="tm-test-card tm-fade">
                <div style="display:flex;gap:2px;margin-bottom:14px;">
                    <?php for($i=0;$i<5;$i++): ?>
                    <i class="fa-solid fa-star" style="color:<?= $i<$rating ? '#f59e0b' : '#e2e8f0' ?>;font-size:0.9rem;"></i>
                    <?php endfor; ?>
                </div>
                <p style="font-size:0.9rem;color:#334155;line-height:1.7;margin-bottom:20px;font-style:italic;">"<?= htmlspecialchars($text) ?>"</p>
                <div style="display:flex;align-items:center;gap:12px;border-top:1px solid #f1f5f9;padding-top:16px;">
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;color:#fff;flex-shrink:0;">
                        <?= strtoupper(substr($name,0,1)) ?>
                    </div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:700;color:#0f172a;"><?= htmlspecialchars($name) ?></div>
                        <div style="font-size:0.78rem;color:#64748b;"><?= htmlspecialchars($role) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php if(!empty($recentPosts)): ?>
<!-- ===== BLOG PREVIEW ===== -->
<section style="padding:96px 0;background:#fff;">
    <div class="tm-container">
        <div style="text-align:center;max-width:600px;margin:0 auto 60px;">
            <div class="tm-label">Insights</div>
            <h2 class="tm-section-title">Latest From Our Blog</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:28px;">
            <?php foreach($recentPosts as $post): ?>
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="tm-card" style="text-decoration:none;display:block;">
                <div style="height:160px;background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:8px;margin:-24px -24px 20px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-newspaper" style="font-size:2.5rem;color:#22c55e;opacity:0.5;"></i>
                </div>
                <span style="font-size:0.7rem;font-weight:600;color:#16a34a;letter-spacing:.08em;text-transform:uppercase;"><?= htmlspecialchars($post['cat_name']??'Blog') ?></span>
                <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:8px 0 10px;line-height:1.4;"><?= htmlspecialchars($post['title']) ?></h3>
                <p style="font-size:0.85rem;color:#64748b;line-height:1.6;"><?= htmlspecialchars(substr(strip_tags($post['excerpt']??''),0,120)) ?>...</p>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="<?= SITE_URL ?>/blog.php" class="tm-btn-outline">View All Articles</a>
        </div>
    </div>
</section>
<?php endif; ?>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
