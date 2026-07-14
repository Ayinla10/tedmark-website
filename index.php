<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle      = 'Helping Businesses Run Smarter With Technology';
$pageHasDarkHero = true;
$pageDesc        = 'Tedmark Digital Agency helps businesses run smarter with technology, automation, business systems, and modern digital infrastructure.';
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

<!-- ===== HERO (centered, minimal) ===== -->
<section class="tm2-hero">
    <video class="tm2-hero-video" autoplay muted loop playsinline poster="">
        <source src="<?= SITE_URL ?>/assets/videos/hero-bg.mp4" type="video/mp4">
    </video>
    <div class="tm2-hero-glow"></div>
    <div class="tm2-badge">
        <i class="fa-solid fa-bolt"></i> <?= cfg($cfg,'hero_badge','Helping Businesses Run Smarter') ?>
    </div>
    <?php
    $heroLine3 = cfg($cfg,'hero_h1_line3','We Grow Businesses.');
    // Italicize the last word for the Bricolage Grotesque accent treatment, but only when using the default copy
    if ($heroLine3 === 'We Grow Businesses.') $heroLine3 = 'We Grow <em>Businesses</em>.';
    ?>
    <h1>
        <?= cfg($cfg,'hero_h1_line1','We Build Systems.') ?><br>
        <?= cfg($cfg,'hero_h1_line2','We Automate Work.') ?><br>
        <?= $heroLine3 ?>
    </h1>
    <p>
        <?= cfg($cfg,'hero_subtext','We help businesses organize, automate, and digitize their operations using smart systems and modern technology so they can save time, reduce costs, and grow without limits.') ?>
    </p>

    <form class="tm2-email-form" id="hero-email-form">
        <input type="email" name="email" placeholder="your@email.com" required>
        <button type="submit"><?= cfg($cfg,'hero_btn_primary','Book a Free Session') ?></button>
    </form>
</section>

<!-- ===== TRUST MARQUEE (separates hero from Problem section) ===== -->
<div class="tm2-marquee">
    <div class="tm2-marquee-label">Our Trusted Partners</div>
    <div class="tm2-marquee-viewport">
        <div class="tm2-marquee-track">
            <?php
            $logos = ['RetailPro GH','MediTrack','EduLink','FoodFlow','PropEstate','LogiMove'];
            // Duplicate the list so the loop is seamless
            foreach(array_merge($logos, $logos) as $l): ?>
            <span><?= $l ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ===== PROBLEMS (connected numbered-node layout) ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">The Problem</div>
            <h2 class="tm2-h2 tm2-problem-title">Sound <em>Familiar</em>?</h2>
            <p class="tm2-sub tm2-problem-sub">Most growing businesses are held back by the same operational bottlenecks. We fix all of them.</p>
        </div>
        <div class="tm2-timeline">
            <?php
            $problems = [
                ['num'=>'01','title'=>'Manual Processes','desc'=>'Hours lost to repetitive tasks that could be automated — data entry, invoicing, reporting.'],
                ['num'=>'02','title'=>'Scattered Information','desc'=>'Customer data, finances, and operations spread across spreadsheets and paper files.'],
                ['num'=>'03','title'=>'Poor Communication','desc'=>'Team silos, missed follow-ups, and inconsistent customer experiences costing you sales.'],
                ['num'=>'04','title'=>'Lack of Visibility','desc'=>'No real-time dashboards or reports — you\'re making decisions without accurate data.'],
            ];
            foreach($problems as $i => $p): ?>
            <div class="tm2-timeline-step">
                <div class="tm2-timeline-num<?= $i===0 ? ' active' : '' ?>"><?= $p['num'] ?></div>
                <h3 class="tm2-problem-card-title"><?= $p['title'] ?></h3>
                <p class="tm2-problem-card-sub"><?= $p['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== SERVICES ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">What We Do</div>
            <h2 class="tm2-h2">Everything Your Business Needs to <em>Thrive</em> Digitally</h2>
            <p class="tm2-sub">End-to-end digital transformation — from strategy to implementation to ongoing support.</p>
        </div>
        <div class="tm2-grid tm2-grid-4">
            <?php
            $services = [
                ['icon'=>'fa-solid fa-gears','title'=>'Business Systems','desc'=>'Custom ERPs, inventory management, HR systems, and operational platforms tailored for your business.'],
                ['icon'=>'fa-solid fa-robot','title'=>'Automation','desc'=>'Automate invoicing, reporting, communications, and workflows to save 10+ hours per week.'],
                ['icon'=>'fa-solid fa-code','title'=>'Web Development','desc'=>'Fast, modern websites and web applications that convert visitors into paying customers.'],
                ['icon'=>'fa-solid fa-cart-shopping','title'=>'E-Commerce','desc'=>'Online stores with payments, inventory, and shipping — ready to sell anywhere in the world.'],
            ];
            foreach($services as $s): ?>
            <div class="tm2-card">
                <div class="tm2-card-icon"><i class="<?= $s['icon'] ?>"></i></div>
                <h3><?= $s['title'] ?></h3>
                <p><?= $s['desc'] ?></p>
                <a href="<?= SITE_URL ?>/services.php" style="font-size:0.85rem;font-weight:600;color:var(--accent);display:inline-flex;align-items:center;gap:6px;margin-top:14px;text-decoration:none;">
                    Learn more <i class="fa-solid fa-arrow-right fa-2xs"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="<?= SITE_URL ?>/services.php" class="tm2-btn tm2-btn-primary">
                View All Services <i class="fa-solid fa-arrow-right fa-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== STATS ===== -->
<section class="tm2-section" style="padding-top:0;">
    <div class="tm2-container tm2-stats">
        <div><div class="num accent"><?= cfg($cfg,'stat_1_value','80+') ?></div><div class="lbl"><?= cfg($cfg,'stat_1_label','Projects Delivered') ?></div></div>
        <div><div class="num accent"><?= cfg($cfg,'stat_2_value','95%') ?></div><div class="lbl"><?= cfg($cfg,'stat_2_label','Client Satisfaction') ?></div></div>
        <div><div class="num accent"><?= cfg($cfg,'stat_3_value','8') ?></div><div class="lbl"><?= cfg($cfg,'stat_3_label','Industries Served') ?></div></div>
        <div><div class="num accent"><?= cfg($cfg,'stat_4_value','3yrs') ?></div><div class="lbl"><?= cfg($cfg,'stat_4_label','In Business') ?></div></div>
    </div>
</section>

<!-- ===== TOOLS ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">Free Tools</div>
            <h2 class="tm2-h2">Try Our Free Business Tools</h2>
            <p class="tm2-sub">Get insights about your business and discover opportunities for growth.</p>
        </div>
        <div class="tm2-grid tm2-grid-3">
            <?php
            $tools = [
                ['icon'=>'fa-solid fa-heart-pulse',       'title'=>'Business Health Checker','desc'=>'Answer a few questions and get a personalized report on your business health.',  'cta'=>'Try Now',              'link'=>'/tools/business-health.php'],
                ['icon'=>'fa-solid fa-calculator',         'title'=>'ROI Calculator',          'desc'=>'Calculate how much time and money your business can save with automation.',      'cta'=>'Calculate Now',        'link'=>'/tools/roi-calculator.php'],
                ['icon'=>'fa-solid fa-wand-magic-sparkles','title'=>'Solution Recommender',    'desc'=>'Tell us about your business and we\'ll recommend the right solutions for you.','cta'=>'Get Recommendations',  'link'=>'/tools/service-recommender.php'],
            ];
            foreach($tools as $t): ?>
            <div class="tm2-card">
                <div class="tm2-card-icon"><i class="<?= $t['icon'] ?>"></i></div>
                <h3><?= $t['title'] ?></h3>
                <p><?= $t['desc'] ?></p>
                <a href="<?= SITE_URL . $t['link'] ?>" style="font-size:0.85rem;font-weight:600;color:var(--accent);display:inline-flex;align-items:center;gap:6px;margin-top:14px;text-decoration:none;">
                    <?= $t['cta'] ?> <i class="fa-solid fa-arrow-right fa-2xs"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== PORTFOLIO ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">Our Work</div>
            <h2 class="tm2-h2">Results We've Delivered</h2>
            <p class="tm2-sub">Real projects, real outcomes for real businesses.</p>
        </div>
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:32px;">
            <?php foreach(['all'=>'All Work','web'=>'Web','systems'=>'Systems','ecommerce'=>'E-Commerce','branding'=>'Branding'] as $k=>$v): ?>
            <button class="tm-filter-btn<?= $k==='all'?' active':'' ?>" data-filter="<?= $k ?>"><?= $v ?></button>
            <?php endforeach; ?>
        </div>
        <div class="tm2-grid tm2-grid-3">
            <?php
            $fallback = [
                ['cat'=>'web',      'icon'=>'fa-solid fa-globe',        'title'=>'RetailPro Website','desc'=>'Modern e-commerce site with 340% increase in online sales.',        'tags'=>['Web','E-Commerce']],
                ['cat'=>'systems',  'icon'=>'fa-solid fa-database',     'title'=>'MediTrack ERP',    'desc'=>'Hospital management system serving 3 facilities across Ghana.',   'tags'=>['Systems','Automation']],
                ['cat'=>'ecommerce','icon'=>'fa-solid fa-cart-shopping', 'title'=>'FoodFlow Store',   'desc'=>'Online ordering platform processing 500+ orders daily.',           'tags'=>['E-Commerce']],
            ];
            $display = !empty($projects) ? $projects : $fallback;
            foreach($display as $proj):
                $isDynamic = isset($proj['category']);
            ?>
            <div class="tm2-card" data-category="<?= $isDynamic ? htmlspecialchars($proj['category']) : $proj['cat'] ?>" style="padding:0;overflow:hidden;">
                <div style="height:160px;background:var(--bg-soft);display:flex;align-items:center;justify-content:center;">
                    <i class="<?= $isDynamic ? 'fa-solid fa-briefcase' : $proj['icon'] ?>" style="font-size:2.2rem;color:var(--accent);opacity:0.7;"></i>
                </div>
                <div style="padding:20px;">
                    <h3 style="margin-bottom:6px;"><?= htmlspecialchars($proj['title']) ?></h3>
                    <p style="margin-bottom:14px;"><?= htmlspecialchars($isDynamic ? ($proj['description']??'') : $proj['desc']) ?></p>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <?php $tags = $isDynamic ? [$proj['category']] : $proj['tags']; foreach($tags as $tag): ?>
                        <span class="tm-port-tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="<?= SITE_URL ?>/portfolio.php" class="tm2-btn tm2-btn-primary">View Full Portfolio <i class="fa-solid fa-arrow-right fa-xs"></i></a>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">The Process</div>
            <h2 class="tm2-h2">How We Work With You</h2>
            <p class="tm2-sub">From first call to launch and beyond — a simple, proven process.</p>
        </div>
        <div class="tm2-grid tm2-grid-4">
            <?php
            $steps = [
                ['num'=>'01','title'=>'Discovery Call','desc'=>'We learn about your business, goals, challenges, and current systems in a free 30-minute consultation.'],
                ['num'=>'02','title'=>'Digital Roadmap','desc'=>'We create a custom plan showing exactly what to build, the timeline, and expected outcomes.'],
                ['num'=>'03','title'=>'Build & Launch','desc'=>'Our team builds your solution with weekly updates and your full involvement throughout.'],
                ['num'=>'04','title'=>'Grow & Scale','desc'=>'Ongoing support, training, and optimisation to ensure you keep getting better results over time.'],
            ];
            foreach($steps as $s): ?>
            <div class="tm2-card" style="text-align:center;">
                <div class="tm2-card-icon" style="margin:0 auto 14px;background:var(--accent);color:var(--accent-ink);font-weight:800;font-size:14px;"><?= $s['num'] ?></div>
                <h3><?= $s['title'] ?></h3>
                <p><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">Client Stories</div>
            <h2 class="tm2-h2">What Our Clients Say</h2>
        </div>
        <div class="tm2-grid tm2-grid-3">
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
            <div class="tm2-card">
                <div style="display:flex;gap:2px;margin-bottom:14px;">
                    <?php for($i=0;$i<5;$i++): ?>
                    <i class="fa-solid fa-star" style="color:<?= $i<$rating ? 'var(--accent)' : 'var(--border)' ?>;font-size:0.85rem;"></i>
                    <?php endfor; ?>
                </div>
                <p style="font-size:0.9rem;color:var(--text);line-height:1.7;margin-bottom:20px;font-style:italic;">"<?= htmlspecialchars($text) ?>"</p>
                <div style="display:flex;align-items:center;gap:12px;border-top:1px solid var(--border);padding-top:16px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--accent);color:var(--accent-ink);display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:600;flex-shrink:0;">
                        <?= strtoupper(substr($name,0,1)) ?>
                    </div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:600;color:var(--text);"><?= htmlspecialchars($name) ?></div>
                        <div style="font-size:0.78rem;color:var(--text-soft);"><?= htmlspecialchars($role) ?></div>
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
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">Insights</div>
            <h2 class="tm2-h2">Latest From Our Blog</h2>
        </div>
        <div class="tm2-grid tm2-grid-3">
            <?php foreach($recentPosts as $post): ?>
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="tm2-card" style="text-decoration:none;display:block;">
                <div style="height:140px;background:var(--bg-soft);border-radius:12px;margin:-24px -24px 18px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-newspaper" style="font-size:2rem;color:var(--accent);opacity:0.6;"></i>
                </div>
                <span style="font-size:0.7rem;font-weight:600;color:var(--accent);letter-spacing:.08em;text-transform:uppercase;"><?= htmlspecialchars($post['category']??'Blog') ?></span>
                <h3 style="margin:8px 0 8px;"><?= htmlspecialchars($post['title']) ?></h3>
                <p><?= htmlspecialchars(substr(strip_tags($post['excerpt']??''),0,120)) ?>...</p>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="<?= SITE_URL ?>/blog.php" class="tm2-btn tm2-btn-outline">View All Articles</a>
        </div>
    </div>
</section>
<?php endif; ?>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
