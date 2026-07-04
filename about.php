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
<section class="tm2-page-hero">
    <div class="tm2-badge"><span></span> About Us</div>
    <h1><?= htmlspecialchars($cfg['about_hero_heading']??'We Help African Businesses Run Smarter') ?></h1>
    <p><?= htmlspecialchars($cfg['about_hero_subtext']??'Tedmark Digital Agency was founded with one purpose: to give African businesses access to the same quality of technology, automation, and digital infrastructure that global companies rely on every day.') ?></p>
    <div class="tm2-stats" style="margin-top:36px;">
        <div><div class="num accent"><?= htmlspecialchars($cfg['stat_1_value']??'80+') ?></div><div class="lbl"><?= htmlspecialchars($cfg['stat_1_label']??'Clients Served') ?></div></div>
        <div><div class="num accent"><?= htmlspecialchars($cfg['stat_4_value']??'5+') ?></div><div class="lbl">Years Experience</div></div>
        <div><div class="num accent"><?= htmlspecialchars($cfg['stat_3_value']??'8') ?></div><div class="lbl"><?= htmlspecialchars($cfg['stat_3_label']??'Industries') ?></div></div>
    </div>
</section>

<!-- MISSION -->
<section class="tm2-section" style="padding-top:0;">
    <div class="tm2-container">
        <div class="tm2-cta-band" style="max-width:680px;margin:0 auto;">
            <div class="tm2-eyebrow" style="justify-content:center;">Our Mission</div>
            <p style="font-size:1.1rem;color:var(--text);line-height:1.75;font-style:italic;">"<?= htmlspecialchars($cfg['about_mission']??'To make enterprise-grade technology accessible to every African business — regardless of size, sector, or location.') ?>"</p>
        </div>
    </div>
</section>

<!-- STORY -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-grid tm2-grid-2" style="align-items:center;">
            <div>
                <div class="tm2-eyebrow">Our Story</div>
                <h2 class="tm2-h2" style="margin-bottom:20px;"><?= htmlspecialchars($cfg['about_story_heading']??'Built From Real Frustration') ?></h2>
                <p style="font-size:1rem;color:var(--text-soft);line-height:1.8;margin-bottom:18px;"><?= htmlspecialchars($cfg['about_story_p1']??'We started Tedmark Digital after watching talented African business owners lose time, money, and customers because they lacked the right systems. Manual invoicing, lost customer data, no inventory visibility — problems that technology solved elsewhere decades ago.') ?></p>
                <?php if(!empty($cfg['about_story_p2'])): ?>
                <p style="font-size:1rem;color:var(--text-soft);line-height:1.8;margin-bottom:18px;"><?= htmlspecialchars($cfg['about_story_p2']) ?></p>
                <?php endif; ?>
                <?php if(!empty($cfg['about_story_p3'])): ?>
                <p style="font-size:1rem;color:var(--text-soft);line-height:1.8;"><?= htmlspecialchars($cfg['about_story_p3']) ?></p>
                <?php endif; ?>
            </div>
            <div class="tm2-grid tm2-grid-2">
                <?php
                $values = [
                    ['icon'=>'fa-solid fa-bullseye','title'=>'Results-first','desc'=>'We measure success by the impact on your business, not hours billed.'],
                    ['icon'=>'fa-solid fa-handshake','title'=>'Long-term Partners','desc'=>'We build relationships, not transactions. We\'re here for your growth journey.'],
                    ['icon'=>'fa-solid fa-lock','title'=>'Transparency','desc'=>'Fixed pricing. No surprises. You always know what you\'re getting.'],
                    ['icon'=>'fa-solid fa-leaf','title'=>'African-first','desc'=>'We design for African realities — local payments, infrastructure, and context.'],
                ];
                foreach($values as $v): ?>
                <div class="tm2-card">
                    <div class="tm2-card-icon" style="width:36px;height:36px;margin-bottom:10px;"><i class="<?= $v['icon'] ?>"></i></div>
                    <h3 style="font-size:0.9rem;margin-bottom:6px;"><?= $v['title'] ?></h3>
                    <p style="font-size:0.8rem;"><?= $v['desc'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- TEAM -->
<section class="tm2-section">
    <div class="tm2-container">
        <div class="tm2-section-head">
            <div class="tm2-eyebrow">The Team</div>
            <h2 class="tm2-h2">The People Behind the Work</h2>
            <p class="tm2-sub">A lean, expert team with deep roots in technology and business.</p>
        </div>
        <div class="tm2-grid tm2-grid-4">
            <?php
            $teamFallback = [
                ['name'=>'Mark Asante','role'=>'Founder &amp; CEO','avatar'=>'','bio'=>'10+ years building technology for African businesses. Leads strategy and client relationships.'],
                ['name'=>'Ama Boateng','role'=>'Lead Developer','avatar'=>'','bio'=>'Full-stack engineer specialising in business systems and automation. 8 years experience.'],
                ['name'=>'Kofi Mensah','role'=>'Design Lead','avatar'=>'','bio'=>'UI/UX designer and brand strategist. Creates the visual systems our clients love.'],
                ['name'=>'Efua Owusu','role'=>'Digital Marketing Lead','avatar'=>'','bio'=>'Data-driven marketer helping clients grow their online presence and drive revenue.'],
            ];
            $displayTeam = !empty($teamRows) ? $teamRows : $teamFallback;
            foreach($displayTeam as $t): ?>
            <div class="tm2-card" style="text-align:center;">
                <?php if(!empty($t['avatar'])): ?>
                <img src="<?= htmlspecialchars($t['avatar']) ?>" alt="<?= htmlspecialchars($t['name']) ?>" style="width:64px;height:64px;border-radius:50%;object-fit:cover;margin:0 auto 14px;display:block;">
                <?php else: ?>
                <div style="width:64px;height:64px;border-radius:50%;background:var(--accent);color:var(--accent-ink);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                    <i class="fa-solid fa-user" style="font-size:1.3rem;"></i>
                </div>
                <?php endif; ?>
                <h3 style="margin-bottom:4px;"><?= htmlspecialchars($t['name']) ?></h3>
                <p style="font-size:0.78rem;font-weight:600;color:var(--accent);margin-bottom:10px;"><?= htmlspecialchars($t['role']??'') ?></p>
                <p><?= htmlspecialchars($t['bio']??$t['desc']??'') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/cta-band.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
