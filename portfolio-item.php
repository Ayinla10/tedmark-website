<?php
require_once __DIR__ . '/includes/config.php';
// Hidden until CMS is ready — redirect to portfolio
header('Location: ' . SITE_URL . '/portfolio.php');
exit;
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

// ── Fallback project data (CMS will replace this via DB) ──────────────────
$fallbackProjects = [
    'retailpro-website' => [
        'slug'       => 'retailpro-website',
        'title'      => 'RetailPro Website',
        'client'     => 'RetailPro GH',
        'year'       => '2024',
        'category'   => 'Web Development',
        'tags'       => ['Web', 'E-Commerce'],
        'icon'       => 'fa-solid fa-globe',
        'color'      => '#60a5fa',
        'bg'         => 'linear-gradient(135deg,#0f172a,#1e3a5f)',
        'headline'   => 'A modern e-commerce platform that tripled sales in 90 days.',
        'desc'       => 'RetailPro GH came to us with an outdated website that was losing customers daily. We rebuilt it from scratch — fast, mobile-first, and conversion-optimised — with an integrated inventory system and seamless checkout.',
        'challenge'  => 'RetailPro had no online presence worth speaking of. Their old website was slow, unresponsive on mobile, and had no e-commerce functionality. They were losing potential customers to competitors every day.',
        'solution'   => 'We designed and built a modern, blazing-fast e-commerce website with product management, cart, checkout, and integration with mobile money payment APIs. We also built a simple admin panel for managing orders and stock.',
        'result'     => '+340% online sales',
        'metrics'    => [
            ['value'=>'+340%','label'=>'Increase in Online Sales'],
            ['value'=>'< 2s', 'label'=>'Average Page Load Time'],
            ['value'=>'3 months','label'=>'Time to Results'],
            ['value'=>'98%',  'label'=>'Mobile Performance Score'],
        ],
        'services'   => ['Web Design & Development', 'E-Commerce Integration', 'Mobile Money API', 'SEO Optimisation'],
        'tech'       => ['PHP', 'MySQL', 'Tailwind CSS', 'JavaScript', 'Paystack API'],
        'testimonial'=> ['text'=>'Tedmark completely transformed our online presence. Orders started coming in within the first week of launch.', 'name'=>'Kwame Mensah', 'role'=>'CEO, RetailPro GH'],
        'next_slug'  => 'meditrack-erp',
        'next_title' => 'MediTrack ERP',
    ],
    'meditrack-erp' => [
        'slug'       => 'meditrack-erp',
        'title'      => 'MediTrack ERP',
        'client'     => 'MediTrack Clinics',
        'year'       => '2024',
        'category'   => 'Business Systems',
        'tags'       => ['Systems', 'Automation'],
        'icon'       => 'fa-solid fa-database',
        'color'      => '#22c55e',
        'bg'         => 'linear-gradient(135deg,#0f172a,#1a2e1a)',
        'headline'   => 'A unified hospital management system across 3 clinic locations.',
        'desc'       => 'MediTrack needed a system that could connect their 3 clinic branches — patient records, staff, billing, and appointments — all in one place. We delivered a custom ERP tailored for Ghanaian healthcare operations.',
        'challenge'  => 'Each clinic branch was running independently with paper records and Excel files. Patient data was duplicated, billing was error-prone, and there was no central visibility for management.',
        'solution'   => 'We built a custom multi-branch ERP system with patient management, appointment scheduling, billing, and real-time reporting dashboards — all accessible from any branch.',
        'result'     => '3 clinics unified',
        'metrics'    => [
            ['value'=>'3','label'=>'Clinics Unified'],
            ['value'=>'70%','label'=>'Reduction in Admin Time'],
            ['value'=>'0','label'=>'Paper Records Needed'],
            ['value'=>'100%','label'=>'Real-time Data Sync'],
        ],
        'services'   => ['Custom ERP Development', 'Multi-branch Architecture', 'Staff Training', 'Ongoing Support'],
        'tech'       => ['PHP', 'MySQL', 'Chart.js', 'REST API', 'Role-based Access Control'],
        'testimonial'=> ['text'=>'We finally have full visibility across all our clinics. The system paid for itself in the first month.', 'name'=>'Dr. Abena Asante', 'role'=>'Managing Director, MediTrack'],
        'next_slug'  => 'foodflow-store',
        'next_title' => 'FoodFlow Store',
    ],
    'foodflow-store' => [
        'slug'       => 'foodflow-store',
        'title'      => 'FoodFlow Store',
        'client'     => 'FoodFlow',
        'year'       => '2023',
        'category'   => 'E-Commerce',
        'tags'       => ['E-Commerce', 'Systems'],
        'icon'       => 'fa-solid fa-cart-shopping',
        'color'      => '#fb923c',
        'bg'         => 'linear-gradient(135deg,#0f172a,#2d1a0f)',
        'headline'   => 'An online food ordering platform processing 500+ orders daily.',
        'desc'       => 'FoodFlow needed a robust ordering platform with real-time kitchen and delivery tracking. We built an end-to-end system that handles ordering, payments, kitchen dispatch, and last-mile delivery coordination.',
        'challenge'  => 'FoodFlow was taking orders over WhatsApp and phone calls — chaotic, error-prone, and impossible to scale. They had no way to track delivery status or kitchen load.',
        'solution'   => 'We built a custom food ordering platform with customer-facing ordering, kitchen dashboard, rider app integration, and a management console — all talking to each other in real-time.',
        'result'     => '500+ daily orders',
        'metrics'    => [
            ['value'=>'500+','label'=>'Orders Processed Daily'],
            ['value'=>'12min','label'=>'Average Order-to-Dispatch'],
            ['value'=>'4.8★','label'=>'Customer Rating'],
            ['value'=>'0','label'=>'WhatsApp Orders Needed'],
        ],
        'services'   => ['Platform Development', 'Real-time Systems', 'Payment Integration', 'Rider App Integration'],
        'tech'       => ['PHP', 'MySQL', 'WebSockets', 'Paystack', 'Google Maps API'],
        'testimonial'=> ['text'=>'Our capacity tripled and we stopped losing orders. This system is the backbone of our business now.', 'name'=>'Ama Boateng', 'role'=>'Founder, FoodFlow'],
        'next_slug'  => 'retailpro-website',
        'next_title' => 'RetailPro Website',
    ],
    'logitrack-dashboard' => [
        'slug'       => 'logitrack-dashboard',
        'title'      => 'LogiMove Dashboard',
        'client'     => 'LogiMove Logistics',
        'year'       => '2023',
        'category'   => 'Systems',
        'tags'       => ['Systems', 'Automation'],
        'icon'       => 'fa-solid fa-chart-bar',
        'color'      => '#f59e0b',
        'bg'         => 'linear-gradient(135deg,#0f172a,#2d2200)',
        'headline'   => 'Real-time fleet tracking and dispatch management — 40% faster.',
        'desc'       => 'LogiMove needed full visibility over their fleet and dispatch operations. We built a real-time tracking dashboard with custom analytics, route optimisation, and a driver mobile app.',
        'challenge'  => 'Dispatching was done via phone calls. There was no visibility on driver locations, delivery status, or performance metrics. Clients were frustrated by lack of updates.',
        'solution'   => 'A web-based fleet dashboard with real-time GPS tracking, automated dispatch, delivery confirmation, and a simple driver app — all synced live.',
        'result'     => '40% faster dispatch',
        'metrics'    => [
            ['value'=>'40%','label'=>'Faster Dispatch Time'],
            ['value'=>'Real-time','label'=>'Fleet Visibility'],
            ['value'=>'98%','label'=>'Delivery Confirmation Rate'],
            ['value'=>'60%','label'=>'Fewer Missed Deliveries'],
        ],
        'services'   => ['Dashboard Development', 'GPS Integration', 'Driver App', 'Analytics & Reporting'],
        'tech'       => ['PHP', 'MySQL', 'Google Maps API', 'WebSockets', 'PWA'],
        'testimonial'=> ['text'=>'We can now see every driver in real-time and our clients love the live delivery updates.', 'name'=>'Kojo Acheampong', 'role'=>'Operations Director, LogiMove'],
        'next_slug'  => 'meditrack-erp',
        'next_title' => 'MediTrack ERP',
    ],
];

// ── Resolve project ───────────────────────────────────────────────────────
$slug    = $_GET['slug'] ?? '';
$project = null;

// 1. Try DB first
try {
    if ($slug) {
        $project = fetchOne("SELECT * FROM projects WHERE slug = ? AND status = 'active'", [$slug]);
    } elseif (isset($_GET['id'])) {
        $project = fetchOne("SELECT * FROM projects WHERE id = ? AND status = 'active'", [(int)$_GET['id']]);
    }
} catch (Exception $e) { $project = null; }

// 2. Fall back to hardcoded data
$isFallback = false;
if (!$project) {
    $project    = $fallbackProjects[$slug] ?? reset($fallbackProjects);
    $isFallback = true;
}

if (!$project) { header('Location: ' . SITE_URL . '/portfolio.php'); exit; }

$pageTitle       = $project['title'] . ' — Portfolio';
$pageDesc        = $isFallback ? $project['desc'] : ($project['description'] ?? '');
$pageHasDarkHero = true;
require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.88) 100%),url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="position:relative;z-index:2;">
        <!-- Breadcrumb -->
        <div style="display:flex;align-items:center;gap:8px;font-size:0.8rem;color:#64748b;margin-bottom:28px;">
            <a href="<?= SITE_URL ?>/" style="color:#64748b;text-decoration:none;">Home</a>
            <i class="fa-solid fa-chevron-right fa-2xs"></i>
            <a href="<?= SITE_URL ?>/portfolio.php" style="color:#64748b;text-decoration:none;">Portfolio</a>
            <i class="fa-solid fa-chevron-right fa-2xs"></i>
            <span style="color:#94a3b8;"><?= htmlspecialchars($project['title']) ?></span>
        </div>

        <div style="display:grid;grid-template-columns:1fr auto;gap:40px;align-items:end;flex-wrap:wrap;">
            <div>
                <!-- Category badge -->
                <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#4ade80;font-size:0.75rem;font-weight:600;padding:5px 14px;border-radius:99px;margin-bottom:20px;text-transform:uppercase;letter-spacing:.06em;">
                    <i class="fa-solid fa-briefcase fa-xs"></i>
                    <?= htmlspecialchars($isFallback ? $project['category'] : ($project['category'] ?? 'Project')) ?>
                </div>
                <h1 style="font-size:clamp(2rem,5vw,3.2rem);font-weight:900;color:#fff;line-height:1.1;margin:0 0 16px;">
                    <?= htmlspecialchars($project['title']) ?>
                </h1>
                <?php if($isFallback && isset($project['headline'])): ?>
                <p style="font-size:1.1rem;color:#94a3b8;max-width:620px;line-height:1.7;"><?= htmlspecialchars($project['headline']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Meta card -->
            <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:24px 28px;min-width:200px;backdrop-filter:blur(8px);">
                <div style="margin-bottom:14px;">
                    <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Client</div>
                    <div style="font-size:0.95rem;font-weight:600;color:#fff;"><?= htmlspecialchars($isFallback ? $project['client'] : ($project['client_name'] ?? 'Client')) ?></div>
                </div>
                <div style="margin-bottom:14px;">
                    <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Year</div>
                    <div style="font-size:0.95rem;font-weight:600;color:#fff;"><?= htmlspecialchars($isFallback ? $project['year'] : date('Y', strtotime($project['created_at'] ?? 'now'))) ?></div>
                </div>
                <div>
                    <div style="font-size:0.7rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Tags</div>
                    <div style="display:flex;gap:5px;flex-wrap:wrap;">
                        <?php
                        $tags = $isFallback ? ($project['tags'] ?? []) : [$project['category'] ?? ''];
                        foreach($tags as $tag): ?>
                        <span style="font-size:0.7rem;font-weight:600;background:rgba(74,222,128,0.12);color:#4ade80;padding:3px 8px;border-radius:6px;"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== MOCKUP / COVER IMAGE ===== -->
<section style="background:#060d1a;padding:0;">
    <div class="tm-container">
        <div style="<?= $isFallback ? 'background:'.$project['bg'] : 'background:linear-gradient(135deg,#0f172a,#1e293b)' ?>;border-radius:0 0 24px 24px;height:380px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,0.4);">
            <!-- grid overlay -->
            <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;"></div>
            <i class="<?= $isFallback ? $project['icon'] : 'fa-solid fa-briefcase' ?>" style="font-size:5rem;color:<?= $isFallback ? $project['color'] : '#22c55e' ?>;opacity:0.35;"></i>
            <!-- Result badge -->
            <?php if($isFallback && isset($project['result'])): ?>
            <div style="position:absolute;bottom:24px;left:32px;background:rgba(0,0,0,0.7);backdrop-filter:blur(8px);border:1px solid rgba(34,197,94,0.3);border-radius:12px;padding:10px 18px;display:flex;align-items:center;gap:10px;">
                <i class="fa-solid fa-arrow-trend-up" style="color:#22c55e;"></i>
                <span style="font-size:0.85rem;font-weight:600;color:#fff;"><?= htmlspecialchars($project['result']) ?></span>
            </div>
            <?php endif; ?>
            <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(to bottom,transparent 60%,rgba(6,13,26,0.8));pointer-events:none;"></div>
        </div>
    </div>
</section>

<!-- ===== MAIN CONTENT ===== -->
<section style="padding:80px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:56px;align-items:start;" class="tm-port-detail-grid">

            <!-- Left: story -->
            <div>

                <!-- Overview -->
                <div style="background:#fff;border-radius:20px;padding:40px;box-shadow:0 2px 16px rgba(0,0,0,.05);margin-bottom:28px;">
                    <h2 style="font-size:1.3rem;font-weight:900;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:10px;">
                        <span style="width:32px;height:32px;background:#dcfce7;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;"><i class="fa-solid fa-file-lines" style="color:#16a34a;font-size:.85rem;"></i></span>
                        Project Overview
                    </h2>
                    <p style="font-size:1rem;color:#475569;line-height:1.85;">
                        <?= htmlspecialchars($isFallback ? $project['desc'] : ($project['description'] ?? '')) ?>
                    </p>
                </div>

                <?php if($isFallback && isset($project['challenge'])): ?>
                <!-- Challenge -->
                <div style="background:#fff;border-radius:20px;padding:40px;box-shadow:0 2px 16px rgba(0,0,0,.05);margin-bottom:28px;">
                    <h2 style="font-size:1.3rem;font-weight:900;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:10px;">
                        <span style="width:32px;height:32px;background:#fee2e2;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;"><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:.85rem;"></i></span>
                        The Challenge
                    </h2>
                    <p style="font-size:1rem;color:#475569;line-height:1.85;"><?= htmlspecialchars($project['challenge']) ?></p>
                </div>

                <!-- Solution -->
                <div style="background:#fff;border-radius:20px;padding:40px;box-shadow:0 2px 16px rgba(0,0,0,.05);margin-bottom:28px;">
                    <h2 style="font-size:1.3rem;font-weight:900;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:10px;">
                        <span style="width:32px;height:32px;background:#dbeafe;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;"><i class="fa-solid fa-lightbulb" style="color:#2563eb;font-size:.85rem;"></i></span>
                        Our Solution
                    </h2>
                    <p style="font-size:1rem;color:#475569;line-height:1.85;"><?= htmlspecialchars($project['solution']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Key Results -->
                <?php if($isFallback && !empty($project['metrics'])): ?>
                <div style="background:#0f172a;border-radius:20px;padding:40px;margin-bottom:28px;">
                    <h2 style="font-size:1.3rem;font-weight:900;color:#fff;margin:0 0 28px;display:flex;align-items:center;gap:10px;">
                        <span style="width:32px;height:32px;background:rgba(74,222,128,0.15);border-radius:8px;display:inline-flex;align-items:center;justify-content:center;"><i class="fa-solid fa-chart-line" style="color:#4ade80;font-size:.85rem;"></i></span>
                        Key Results
                    </h2>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                        <?php foreach($project['metrics'] as $m): ?>
                        <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:22px;">
                            <div style="font-size:2rem;font-weight:900;color:#4ade80;line-height:1;margin-bottom:6px;"><?= htmlspecialchars($m['value']) ?></div>
                            <div style="font-size:0.82rem;color:#94a3b8;font-weight:500;"><?= htmlspecialchars($m['label']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Testimonial -->
                <?php if($isFallback && isset($project['testimonial'])): $t = $project['testimonial']; ?>
                <div style="background:#fff;border-left:4px solid #16a34a;border-radius:0 20px 20px 0;padding:32px 36px;box-shadow:0 2px 16px rgba(0,0,0,.05);">
                    <i class="fa-solid fa-quote-left" style="font-size:1.5rem;color:#dcfce7;margin-bottom:16px;display:block;"></i>
                    <p style="font-size:1.05rem;color:#334155;line-height:1.8;font-style:italic;margin-bottom:20px;">"<?= htmlspecialchars($t['text']) ?>"</p>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);display:flex;align-items:center;justify-content:center;font-size:0.9rem;font-weight:600;color:#fff;">
                            <?= strtoupper(substr($t['name'],0,1)) ?>
                        </div>
                        <div>
                            <div style="font-size:0.9rem;font-weight:600;color:#0f172a;"><?= htmlspecialchars($t['name']) ?></div>
                            <div style="font-size:0.78rem;color:#64748b;"><?= htmlspecialchars($t['role']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right: sidebar -->
            <div style="display:flex;flex-direction:column;gap:24px;position:sticky;top:120px;">

                <!-- Services delivered -->
                <?php if($isFallback && !empty($project['services'])): ?>
                <div style="background:#fff;border-radius:20px;padding:28px;box-shadow:0 2px 16px rgba(0,0,0,.05);">
                    <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin:0 0 18px;text-transform:uppercase;letter-spacing:.05em;">Services Delivered</h3>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <?php foreach($project['services'] as $svc): ?>
                        <div style="display:flex;align-items:center;gap:10px;font-size:0.875rem;color:#334155;">
                            <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:.85rem;flex-shrink:0;"></i>
                            <?= htmlspecialchars($svc) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tech stack -->
                <?php if($isFallback && !empty($project['tech'])): ?>
                <div style="background:#fff;border-radius:20px;padding:28px;box-shadow:0 2px 16px rgba(0,0,0,.05);">
                    <h3 style="font-size:0.95rem;font-weight:800;color:#0f172a;margin:0 0 18px;text-transform:uppercase;letter-spacing:.05em;">Tech Stack</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <?php foreach($project['tech'] as $tech): ?>
                        <span style="font-size:0.78rem;font-weight:500;background:#f1f5f9;color:#334155;padding:5px 12px;border-radius:8px;border:1px solid #e2e8f0;"><?= htmlspecialchars($tech) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- CTA -->
                <div style="background:linear-gradient(135deg,#166534,#14532d);border-radius:20px;padding:28px;text-align:center;position:relative;overflow:hidden;">
                    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.04) 1px,transparent 1px);background-size:30px 30px;pointer-events:none;"></div>
                    <i class="fa-solid fa-rocket" style="font-size:1.8rem;color:#4ade80;margin-bottom:14px;display:block;position:relative;z-index:1;"></i>
                    <h3 style="font-size:1rem;font-weight:800;color:#fff;margin:0 0 8px;position:relative;z-index:1;">Want Similar Results?</h3>
                    <p style="font-size:0.8rem;color:#86efac;margin:0 0 20px;line-height:1.6;position:relative;z-index:1;">Let's talk about what we can build for your business.</p>
                    <a href="<?= SITE_URL ?>/consultation.php" style="display:block;background:#f59e0b;color:#0f172a;padding:12px;border-radius:10px;font-weight:800;font-size:0.875rem;text-decoration:none;position:relative;z-index:1;">
                        Book a Free Session <i class="fa-solid fa-arrow-right fa-xs"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ===== NEXT PROJECT ===== -->
<?php if($isFallback && isset($project['next_slug'])): ?>
<section style="background:#fff;padding:48px 0;border-top:1px solid #f1f5f9;">
    <div class="tm-container">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;">
            <div>
                <div style="font-size:0.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Next Project</div>
                <div style="font-size:1.2rem;font-weight:800;color:#0f172a;"><?= htmlspecialchars($project['next_title']) ?></div>
            </div>
            <a href="<?= SITE_URL ?>/portfolio-item.php?slug=<?= urlencode($project['next_slug']) ?>" class="tm-btn-primary">
                View Project <i class="fa-solid fa-arrow-right fa-xs"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
@media(max-width:900px){ .tm-port-detail-grid{ grid-template-columns:1fr !important; } }
</style>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
