<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle   = 'About Us';
$pageDesc    = 'Learn about Tedmark Digital Agency — our mission, values, team, and why we are the trusted partner for African businesses running smarter with technology.';
$pageSeoPage = 'about';

try {
    $rows = fetchAll("SELECT `key`, `value` FROM settings");
    $cfg  = array_column($rows, 'value', 'key');
} catch(Exception $e) { $cfg = []; }

try { $teamRows = fetchAll("SELECT * FROM team_members WHERE status='active' ORDER BY sort_order ASC"); }
catch(Exception $e) { $teamRows = []; }

require_once __DIR__ . '/includes/header.php';
?>

<!-- PAGE HERO -->
<section class="tm-page-hero" style="background:linear-gradient(135deg,rgba(6,11,24,0.93) 0%,rgba(10,22,40,0.90) 100%),url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1600&q=80&auto=format&fit=crop') center/cover no-repeat;">
    <div class="tm-container" style="position:relative;z-index:2;display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;">
        <div>
            <div class="tm-label">About Us</div>
            <h1 style="font-size:clamp(2rem,4vw,2.8rem);font-weight:900;color:#fff;margin:16px 0 20px;line-height:1.2;">We Help African Businesses Run Smarter</h1>
            <p style="font-size:1.05rem;color:#94a3b8;line-height:1.75;margin-bottom:32px;">Tedmark Digital Agency was founded with one purpose: to give African businesses access to the same quality of technology, automation, and digital infrastructure that global companies rely on every day.</p>
            <div style="display:flex;gap:32px;">
                <div><div style="font-size:2rem;font-weight:900;color:#fff;"><?= htmlspecialchars($cfg['stat_1_value']??'80+') ?></div><div style="font-size:0.8rem;color:#475569;"><?= htmlspecialchars($cfg['stat_1_label']??'Clients Served') ?></div></div>
                <div style="width:1px;background:#1e293b;"></div>
                <div><div style="font-size:2rem;font-weight:900;color:#fff;"><?= htmlspecialchars($cfg['stat_4_value']??'5+') ?></div><div style="font-size:0.8rem;color:#475569;">Years Experience</div></div>
                <div style="width:1px;background:#1e293b;"></div>
                <div><div style="font-size:2rem;font-weight:900;color:#fff;"><?= htmlspecialchars($cfg['stat_3_value']??'8') ?></div><div style="font-size:0.8rem;color:#475569;"><?= htmlspecialchars($cfg['stat_3_label']??'Industries') ?></div></div>
            </div>
        </div>
        <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:32px;">
            <div style="font-size:0.75rem;font-weight:800;color:#22c55e;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px;">Our Mission</div>
            <p style="font-size:1.1rem;color:#e2e8f0;line-height:1.75;font-style:italic;">"To make enterprise-grade technology accessible to every African business — regardless of size, sector, or location."</p>
        </div>
    </div>
</section>

<!-- STORY -->
<section style="padding:96px 0;background:#fff;">
    <div class="tm-container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;" class="tm-fade">
            <div>
                <div class="tm-label">Our Story</div>
                <h2 class="tm-section-title" style="margin-bottom:20px;">Built From Real Frustration</h2>
                <p style="font-size:1rem;color:#334155;line-height:1.8;margin-bottom:18px;">We started Tedmark Digital after watching talented African business owners lose time, money, and customers because they lacked the right systems. Manual invoicing, lost customer data, no inventory visibility — problems that technology solved elsewhere decades ago.</p>
                <p style="font-size:1rem;color:#334155;line-height:1.8;margin-bottom:18px;">We decided to close that gap. We've since helped over 80 businesses across Ghana, Nigeria, Kenya, and beyond transform their operations with custom technology that fits their exact context and budget.</p>
                <p style="font-size:1rem;color:#334155;line-height:1.8;">We don't sell generic software. We build what each business actually needs — and we stay to make sure it works.</p>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <?php
                $values = [
                    ['icon'=>'fa-solid fa-bullseye','color'=>'#22c55e','title'=>'Results-first','desc'=>'We measure success by the impact on your business, not hours billed.'],
                    ['icon'=>'fa-solid fa-handshake','color'=>'#60a5fa','title'=>'Long-term Partners','desc'=>'We build relationships, not transactions. We\'re here for your growth journey.'],
                    ['icon'=>'fa-solid fa-lock','color'=>'#a78bfa','title'=>'Transparency','desc'=>'Fixed pricing. No surprises. You always know what you\'re getting.'],
                    ['icon'=>'fa-solid fa-leaf','color'=>'#f59e0b','title'=>'African-first','desc'=>'We design for African realities — local payments, infrastructure, and context.'],
                ];
                foreach($values as $v): ?>
                <div class="tm-card" style="padding:20px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(34,197,94,0.08);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                        <i class="<?= $v['icon'] ?>" style="color:<?= $v['color'] ?>;font-size:1rem;"></i>
                    </div>
                    <h3 style="font-size:0.9rem;font-weight:800;color:#0f172a;margin-bottom:6px;"><?= $v['title'] ?></h3>
                    <p style="font-size:0.8rem;color:#64748b;line-height:1.55;"><?= $v['desc'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- TEAM -->
<section style="padding:80px 0;background:#f8fafc;">
    <div class="tm-container">
        <div style="text-align:center;max-width:600px;margin:0 auto 56px;">
            <div class="tm-label">The Team</div>
            <h2 class="tm-section-title">The People Behind the Work</h2>
            <p class="tm-section-sub">A lean, expert team with deep roots in technology and business.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:28px;">
            <?php
            $teamFallback = [
                ['name'=>'Mark Asante','role'=>'Founder &amp; CEO','avatar'=>'','bio'=>'10+ years building technology for African businesses. Leads strategy and client relationships.'],
                ['name'=>'Ama Boateng','role'=>'Lead Developer','avatar'=>'','bio'=>'Full-stack engineer specialising in business systems and automation. 8 years experience.'],
                ['name'=>'Kofi Mensah','role'=>'Design Lead','avatar'=>'','bio'=>'UI/UX designer and brand strategist. Creates the visual systems our clients love.'],
                ['name'=>'Efua Owusu','role'=>'Digital Marketing Lead','avatar'=>'','bio'=>'Data-driven marketer helping clients grow their online presence and drive revenue.'],
            ];
            $displayTeam = !empty($teamRows) ? $teamRows : $teamFallback;
            foreach($displayTeam as $t): ?>
            <div class="tm-card tm-fade" style="text-align:center;">
                <?php if(!empty($t['avatar'])): ?>
                <img src="<?= htmlspecialchars($t['avatar']) ?>" alt="<?= htmlspecialchars($t['name']) ?>" style="width:72px;height:72px;border-radius:50%;object-fit:cover;margin:0 auto 16px;display:block;">
                <?php else: ?>
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fa-solid fa-user" style="font-size:1.5rem;color:#fff;"></i>
                </div>
                <?php endif; ?>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:4px;"><?= htmlspecialchars($t['name']) ?></h3>
                <p style="font-size:0.78rem;font-weight:600;color:#16a34a;margin-bottom:12px;"><?= htmlspecialchars($t['role']??'') ?></p>
                <p style="font-size:0.85rem;color:#64748b;line-height:1.6;"><?= htmlspecialchars($t['bio']??$t['desc']??'') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<style>
@media(max-width:768px){
    .tm-page-hero .tm-container { grid-template-columns:1fr !important; }
    section .tm-container > div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
