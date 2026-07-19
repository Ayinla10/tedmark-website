<?php
$pageTitle = 'Settings';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// ── ALL LOGIC BEFORE LAYOUT ──────────────────────────
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $existing = fetchOne("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($existing) {
                query("UPDATE settings SET `value` = ? WHERE `key` = ?", [trim($value), $key]);
            } else {
                query("INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, 'general')", [$key, trim($value)]);
            }
        }
        header('Location: ' . SITE_URL . '/admin/settings.php?saved=1');
        exit;
    } catch (Exception $e) {
        $error = 'Error saving settings: ' . $e->getMessage();
    }
}
$success = isset($_GET['saved']) ? 'Settings saved successfully!' : '';
// VERSION CHECK: v2

// Load all settings
$rows = fetchAll("SELECT `key`, `value` FROM settings");
$s = [];
foreach ($rows as $r) $s[$r['key']] = $r['value'];

function si($s, $key) { return htmlspecialchars($s[$key] ?? ''); }

// ── NOW OUTPUT HTML ───────────────────────────────────
require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fa-solid fa-times"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST" action="<?= SITE_URL ?>/admin/settings.php">

<!-- TABS -->
<div style="display:flex;gap:4px;margin-bottom:24px;border-bottom:1px solid #1e293b;">
  <?php foreach(['general'=>'General','announce'=>'Announcement Bar','homepage'=>'Homepage','services'=>'Services Page','solutions'=>'Solutions Page','consultation'=>'Consultation Page','tools'=>'Tools Pages','cta'=>'CTA Band','footer'=>'Footer','social'=>'Social Media'] as $tab=>$label): ?>
  <button type="button" class="tab-btn" data-tab="<?= $tab ?>" style="padding:10px 18px;border:none;background:none;color:#64748b;font-size:0.85rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:'Inter',sans-serif;">
    <?= $label ?>
  </button>
  <?php endforeach; ?>
</div>

<!-- GENERAL -->
<div class="tab-panel" id="tab-general">
  <div class="tm-grid-2" style="gap:24px;">
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-globe text-green" style="margin-right:8px;"></i>Site Identity</span></div>
      <div class="form-group"><label>Site Name</label><input type="text" name="settings[site_name]" value="<?= si($s,'site_name') ?>"></div>
      <div class="form-group"><label>Tagline</label><input type="text" name="settings[site_tagline]" value="<?= si($s,'site_tagline') ?>"></div>
      <div class="form-group"><label>Site Logo URL</label><input type="text" name="settings[site_logo]" value="<?= si($s,'site_logo') ?>" placeholder="/assets/images/tedmark logo copy2.png"></div>
    </div>
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-address-card text-green" style="margin-right:8px;"></i>Contact Info</span></div>
      <div class="form-group"><label>Email Address</label><input type="email" name="settings[site_email]" value="<?= si($s,'site_email') ?>"></div>
      <div class="form-group"><label>Phone Number</label><input type="text" name="settings[site_phone]" value="<?= si($s,'site_phone') ?>"></div>
      <div class="form-group"><label>Address</label><input type="text" name="settings[site_address]" value="<?= si($s,'site_address') ?>"></div>
    </div>
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-chart-line text-green" style="margin-right:8px;"></i>Analytics & Tracking</span></div>
      <div class="form-group"><label>Google Analytics ID</label><input type="text" name="settings[google_analytics]" value="<?= si($s,'google_analytics') ?>" placeholder="G-XXXXXXXXXX"></div>
      <div class="form-group"><label>Consultation Page URL</label><input type="text" name="settings[consultation_url]" value="<?= si($s,'consultation_url') ?>"></div>
    </div>
  </div>
</div>

<!-- ANNOUNCEMENT BAR -->
<div class="tab-panel" id="tab-announce" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-bullhorn text-green" style="margin-right:8px;"></i>Announcement Bar (green strip above the nav, shown on every page)</span></div>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:16px;">
      <input type="hidden" name="settings[announce_enabled]" value="0">
      <input type="checkbox" name="settings[announce_enabled]" value="1" <?= ($s['announce_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
      <span>Show announcement bar</span>
    </label>
    <div class="form-group"><label>Text (before the bold part)</label><input type="text" name="settings[announce_text]" value="<?= si($s,'announce_text') ?: 'Wondering if your business is AI-ready?' ?>"></div>
    <div class="form-group"><label>Bold Text (linked, underlined)</label><input type="text" name="settings[announce_bold_text]" value="<?= si($s,'announce_bold_text') ?: 'Take our free 3-minute Business Health Scan' ?>"></div>
    <div class="form-group"><label>Link URL</label><input type="text" name="settings[announce_link]" value="<?= si($s,'announce_link') ?: '/tools/business-health.php' ?>" placeholder="/tools/business-health.php or https://..."></div>
    <p style="color:#64748b;font-size:0.8rem;margin-top:8px;">Preview: <span style="background:#16a34a;color:#fff;padding:6px 12px;border-radius:6px;display:inline-block;font-size:0.85rem;">● <?= si($s,'announce_text') ?: 'Wondering if your business is AI-ready?' ?> <strong><?= si($s,'announce_bold_text') ?: 'Take our free 3-minute Business Health Scan' ?></strong> →</span></p>
  </div>
</div>

<!-- HOMEPAGE -->
<div class="tab-panel" id="tab-homepage" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-image text-green" style="margin-right:8px;"></i>Hero Section</span></div>
    <div class="form-group"><label>Badge Text</label><input type="text" name="settings[hero_badge]" value="<?= si($s,'hero_badge') ?>"></div>
    <div class="form-row">
      <div class="form-group"><label>Headline Line 1</label><input type="text" name="settings[hero_h1_line1]" value="<?= si($s,'hero_h1_line1') ?>"></div>
      <div class="form-group"><label>Headline Line 2</label><input type="text" name="settings[hero_h1_line2]" value="<?= si($s,'hero_h1_line2') ?>"></div>
    </div>
    <div class="form-group"><label>Headline Line 3 (Green gradient)</label><input type="text" name="settings[hero_h1_line3]" value="<?= si($s,'hero_h1_line3') ?>"></div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[hero_subtext]" rows="3"><?= si($s,'hero_subtext') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Primary Button Text</label><input type="text" name="settings[hero_btn_primary]" value="<?= si($s,'hero_btn_primary') ?>"></div>
      <div class="form-group"><label>Secondary Button Text</label><input type="text" name="settings[hero_btn_secondary]" value="<?= si($s,'hero_btn_secondary') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Background Image URL</label><input type="text" name="settings[hero_bg_image]" value="<?= si($s,'hero_bg_image') ?>" placeholder="https://images.unsplash.com/..."></div>
      <div class="form-group"><label>Overlay Opacity (0.0 - 1.0)</label><input type="text" name="settings[hero_overlay_opacity]" value="<?= si($s,'hero_overlay_opacity') ?: '0.92' ?>" placeholder="0.92"></div>
    </div>
  </div>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-chart-bar text-green" style="margin-right:8px;"></i>Stats Bar</span></div>
    <div class="tm-grid-4">
      <?php for($i=1;$i<=4;$i++): ?>
      <div>
        <div class="form-group"><label>Stat <?= $i ?> Value</label><input type="text" name="settings[stat_<?= $i ?>_value]" value="<?= si($s,"stat_{$i}_value") ?>"></div>
        <div class="form-group"><label>Stat <?= $i ?> Label</label><input type="text" name="settings[stat_<?= $i ?>_label]" value="<?= si($s,"stat_{$i}_label") ?>"></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-bars-staggered text-green" style="margin-right:8px;"></i>Trust Marquee (green scrolling band)</span></div>
    <div class="form-row">
      <div class="form-group"><label>Label Line 1</label><input type="text" name="settings[hp_marquee_label1]" value="<?= si($s,'hp_marquee_label1') ?>" placeholder="Our Trusted"></div>
      <div class="form-group"><label>Label Line 2 (bigger, italic)</label><input type="text" name="settings[hp_marquee_label2]" value="<?= si($s,'hp_marquee_label2') ?>" placeholder="Partners"></div>
    </div>
    <div class="form-group"><label>Logos / Client Names (comma separated)</label><input type="text" name="settings[hp_marquee_logos]" value="<?= si($s,'hp_marquee_logos') ?>" placeholder="RetailPro GH, MediTrack, EduLink, FoodFlow, PropEstate, LogiMove"></div>
  </div>

  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-triangle-exclamation text-green" style="margin-right:8px;"></i>Problem Section</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[hp_problem_eyebrow]" value="<?= si($s,'hp_problem_eyebrow') ?>" placeholder="The Problem"></div>
      <div class="form-group"><label>Heading (before emphasis)</label><input type="text" name="settings[hp_problem_h2_pre]" value="<?= si($s,'hp_problem_h2_pre') ?>" placeholder="Sound"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Heading Emphasis Word</label><input type="text" name="settings[hp_problem_h2_em]" value="<?= si($s,'hp_problem_h2_em') ?>" placeholder="Familiar"></div>
      <div class="form-group"><label>Heading Suffix (e.g. ?)</label><input type="text" name="settings[hp_problem_h2_post]" value="<?= si($s,'hp_problem_h2_post') ?>" placeholder="?"></div>
    </div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[hp_problem_subtext]" rows="2"><?= si($s,'hp_problem_subtext') ?></textarea></div>
    <?php for($i=1;$i<=4;$i++): ?>
    <div class="form-row" style="border-top:1px solid #1e293b;padding-top:14px;margin-top:14px;">
      <div class="form-group"><label>Card <?= $i ?> Title</label><input type="text" name="settings[hp_problem_<?= $i ?>_title]" value="<?= si($s,"hp_problem_{$i}_title") ?>"></div>
      <div class="form-group"><label>Card <?= $i ?> Description</label><input type="text" name="settings[hp_problem_<?= $i ?>_desc]" value="<?= si($s,"hp_problem_{$i}_desc") ?>"></div>
    </div>
    <?php endfor; ?>
  </div>

  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-briefcase text-green" style="margin-right:8px;"></i>Services Section Heading</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[hp_services_eyebrow]" value="<?= si($s,'hp_services_eyebrow') ?>" placeholder="What We Do"></div>
      <div class="form-group"><label>Heading (before emphasis)</label><input type="text" name="settings[hp_services_h2_pre]" value="<?= si($s,'hp_services_h2_pre') ?>" placeholder="Everything Your Business Needs to Run on"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Heading Emphasis Word</label><input type="text" name="settings[hp_services_h2_em]" value="<?= si($s,'hp_services_h2_em') ?>" placeholder="AI"></div>
      <div class="form-group"><label>Subtext</label><input type="text" name="settings[hp_services_subtext]" value="<?= si($s,'hp_services_subtext') ?>" placeholder="End-to-end AI transformation..."></div>
    </div>
    <p style="color:#64748b;font-size:0.8rem;">The 4 service cards shown here come from <a href="<?= SITE_URL ?>/admin/services.php" style="color:#22c55e;">Services</a> (first 4 active).</p>
  </div>

  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-wand-magic-sparkles text-green" style="margin-right:8px;"></i>Free Tools Section</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[hp_tools_eyebrow]" value="<?= si($s,'hp_tools_eyebrow') ?>" placeholder="Free Tools"></div>
      <div class="form-group"><label>Heading</label><input type="text" name="settings[hp_tools_h2]" value="<?= si($s,'hp_tools_h2') ?>" placeholder="Try Our Free Business Tools"></div>
    </div>
    <div class="form-group"><label>Subtext</label><input type="text" name="settings[hp_tools_subtext]" value="<?= si($s,'hp_tools_subtext') ?>"></div>
    <?php
    $toolDefaults = [1=>['Business Health Checker','Answer a few questions and get a personalized report on your business health.','Try Now'],2=>['ROI Calculator','Calculate how much time and money your business can save with automation.','Calculate Now'],3=>['Solution Recommender','Tell us about your business and we\'ll recommend the right solutions for you.','Get Recommendations']];
    foreach($toolDefaults as $i=>$d): ?>
    <div style="border-top:1px solid #1e293b;padding-top:14px;margin-top:14px;">
      <div class="form-row">
        <div class="form-group"><label>Tool <?= $i ?> Title</label><input type="text" name="settings[hp_tool_<?= $i ?>_title]" value="<?= si($s,"hp_tool_{$i}_title") ?>" placeholder="<?= $d[0] ?>"></div>
        <div class="form-group"><label>Tool <?= $i ?> Button Text</label><input type="text" name="settings[hp_tool_<?= $i ?>_cta]" value="<?= si($s,"hp_tool_{$i}_cta") ?>" placeholder="<?= $d[2] ?>"></div>
      </div>
      <div class="form-group"><label>Tool <?= $i ?> Description</label><input type="text" name="settings[hp_tool_<?= $i ?>_desc]" value="<?= si($s,"hp_tool_{$i}_desc") ?>" placeholder="<?= $d[1] ?>"></div>
    </div>
    <?php endforeach; ?>
    <p style="color:#64748b;font-size:0.8rem;margin-top:10px;">Links and icons are fixed (tied to the actual tool pages); only text is editable here.</p>
  </div>

  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-folder-open text-green" style="margin-right:8px;"></i>Portfolio Section Heading</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[hp_portfolio_eyebrow]" value="<?= si($s,'hp_portfolio_eyebrow') ?>" placeholder="Our Work"></div>
      <div class="form-group"><label>Heading</label><input type="text" name="settings[hp_portfolio_h2]" value="<?= si($s,'hp_portfolio_h2') ?>" placeholder="Results We've Delivered"></div>
    </div>
    <div class="form-group"><label>Subtext</label><input type="text" name="settings[hp_portfolio_subtext]" value="<?= si($s,'hp_portfolio_subtext') ?>"></div>
    <p style="color:#64748b;font-size:0.8rem;">Project cards come from <a href="<?= SITE_URL ?>/admin/projects.php" style="color:#22c55e;">Projects</a>.</p>
  </div>

  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-diagram-project text-green" style="margin-right:8px;"></i>How It Works (Process)</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[hp_process_eyebrow]" value="<?= si($s,'hp_process_eyebrow') ?>" placeholder="The Process"></div>
      <div class="form-group"><label>Heading</label><input type="text" name="settings[hp_process_h2]" value="<?= si($s,'hp_process_h2') ?>" placeholder="How We Work With You"></div>
    </div>
    <div class="form-group"><label>Subtext</label><input type="text" name="settings[hp_process_subtext]" value="<?= si($s,'hp_process_subtext') ?>"></div>
    <?php
    $stepDefaults = [1=>['Discovery Call','We learn about your business, goals, challenges, and current systems in a free 30-minute consultation.'],2=>['Digital Roadmap','We create a custom plan showing exactly what to build, the timeline, and expected outcomes.'],3=>['Build & Launch','Our team builds your solution with weekly updates and your full involvement throughout.'],4=>['Grow & Scale','Ongoing support, training, and optimisation to ensure you keep getting better results over time.']];
    foreach($stepDefaults as $i=>$d): ?>
    <div class="form-row" style="border-top:1px solid #1e293b;padding-top:14px;margin-top:14px;">
      <div class="form-group"><label>Step <?= $i ?> Title</label><input type="text" name="settings[hp_process_<?= $i ?>_title]" value="<?= si($s,"hp_process_{$i}_title") ?>" placeholder="<?= $d[0] ?>"></div>
      <div class="form-group"><label>Step <?= $i ?> Description</label><input type="text" name="settings[hp_process_<?= $i ?>_desc]" value="<?= si($s,"hp_process_{$i}_desc") ?>" placeholder="<?= $d[1] ?>"></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-star text-green" style="margin-right:8px;"></i>Testimonials Section Heading</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[hp_testimonials_eyebrow]" value="<?= si($s,'hp_testimonials_eyebrow') ?>" placeholder="Client Stories"></div>
      <div class="form-group"><label>Heading</label><input type="text" name="settings[hp_testimonials_h2]" value="<?= si($s,'hp_testimonials_h2') ?>" placeholder="What Our Clients Say"></div>
    </div>
    <p style="color:#64748b;font-size:0.8rem;">Testimonial cards come from <a href="<?= SITE_URL ?>/admin/testimonials.php" style="color:#22c55e;">Testimonials</a>.</p>
  </div>

  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-newspaper text-green" style="margin-right:8px;"></i>Blog Preview Section Heading</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[hp_blog_eyebrow]" value="<?= si($s,'hp_blog_eyebrow') ?>" placeholder="Insights"></div>
      <div class="form-group"><label>Heading</label><input type="text" name="settings[hp_blog_h2]" value="<?= si($s,'hp_blog_h2') ?>" placeholder="Latest From Our Blog"></div>
    </div>
    <p style="color:#64748b;font-size:0.8rem;">Only shown when there are published posts. Posts come from <a href="<?= SITE_URL ?>/admin/posts.php" style="color:#22c55e;">Blog Posts</a>.</p>
  </div>
</div>

<!-- SERVICES PAGE -->
<div class="tab-panel" id="tab-services" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-layer-group text-green" style="margin-right:8px;"></i>Hero Section</span></div>
    <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[svc_hero_eyebrow]" value="<?= si($s,'svc_hero_eyebrow') ?>" placeholder="6 Pillars • One Operating System"></div>
    <div class="form-row">
      <div class="form-group"><label>Headline Part 1 (line 1)</label><input type="text" name="settings[svc_hero_h1_pre]" value="<?= si($s,'svc_hero_h1_pre') ?>" placeholder="Pick a pillar."></div>
      <div class="form-group"><label>Headline Part 2 (before emphasis)</label><input type="text" name="settings[svc_hero_h1_mid]" value="<?= si($s,'svc_hero_h1_mid') ?>" placeholder="Or"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Emphasised Word (italic accent)</label><input type="text" name="settings[svc_hero_h1_em]" value="<?= si($s,'svc_hero_h1_em') ?>" placeholder="combine"></div>
      <div class="form-group"><label>Headline Part 3 (after emphasis)</label><input type="text" name="settings[svc_hero_h1_post]" value="<?= si($s,'svc_hero_h1_post') ?>" placeholder="them all."></div>
    </div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[svc_hero_subtext]" rows="3"><?= si($s,'svc_hero_subtext') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Primary Button Text</label><input type="text" name="settings[svc_hero_btn_primary]" value="<?= si($s,'svc_hero_btn_primary') ?>" placeholder="Get Started Today"></div>
      <div class="form-group"><label>Secondary Button Text</label><input type="text" name="settings[svc_hero_btn_secondary]" value="<?= si($s,'svc_hero_btn_secondary') ?>" placeholder="Explore the Pillars"></div>
    </div>
  </div>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-chart-bar text-green" style="margin-right:8px;"></i>Hero Stats (3)</span></div>
    <div class="tm-grid-3">
      <?php for($i=1;$i<=3;$i++): ?>
      <div>
        <div class="form-group"><label>Stat <?= $i ?> Value</label><input type="text" name="settings[svc_stat_<?= $i ?>_value]" value="<?= si($s,"svc_stat_{$i}_value") ?>"></div>
        <div class="form-group"><label>Stat <?= $i ?> Label</label><input type="text" name="settings[svc_stat_<?= $i ?>_label]" value="<?= si($s,"svc_stat_{$i}_label") ?>"></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-cubes text-green" style="margin-right:8px;"></i>Pillars Grid Heading</span></div>
    <div class="form-row">
      <div class="form-group"><label>Heading Line 1</label><input type="text" name="settings[svc_pillars_h2_pre]" value="<?= si($s,'svc_pillars_h2_pre') ?>" placeholder="Six specialised pillars."></div>
      <div class="form-group"><label>Heading Line 2 (italic accent)</label><input type="text" name="settings[svc_pillars_h2_em]" value="<?= si($s,'svc_pillars_h2_em') ?>" placeholder="Endless combinations."></div>
    </div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[svc_pillars_subtext]" rows="2"><?= si($s,'svc_pillars_subtext') ?></textarea></div>
    <p style="color:#64748b;font-size:0.8rem;">The 6 pillar cards themselves are managed in <a href="<?= SITE_URL ?>/admin/services.php" style="color:#22c55e;">Services</a>.</p>
  </div>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-thumbs-up text-green" style="margin-right:8px;"></i>Why These Services</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[svc_why_eyebrow]" value="<?= si($s,'svc_why_eyebrow') ?>" placeholder="Why Businesses Choose Tedmark"></div>
      <div class="form-group"><label>Right-side Tag</label><input type="text" name="settings[svc_why_tag]" value="<?= si($s,'svc_why_tag') ?>" placeholder="Speed, Result, Support"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Heading Line 1</label><input type="text" name="settings[svc_why_h2_pre]" value="<?= si($s,'svc_why_h2_pre') ?>" placeholder="Built for results."></div>
      <div class="form-group"><label>Heading Line 2 (italic accent)</label><input type="text" name="settings[svc_why_h2_em]" value="<?= si($s,'svc_why_h2_em') ?>" placeholder="Built to last."></div>
    </div>
    <?php for($i=1;$i<=3;$i++): ?>
    <div class="form-row" style="border-top:1px solid #1e293b;padding-top:14px;margin-top:14px;">
      <div class="form-group"><label>Column <?= $i ?> Title</label><input type="text" name="settings[svc_why_<?= $i ?>_title]" value="<?= si($s,"svc_why_{$i}_title") ?>"></div>
      <div class="form-group"><label>Column <?= $i ?> Description</label><input type="text" name="settings[svc_why_<?= $i ?>_desc]" value="<?= si($s,"svc_why_{$i}_desc") ?>"></div>
    </div>
    <?php endfor; ?>
  </div>
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-circle-question text-green" style="margin-right:8px;"></i>FAQ Section</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[svc_faq_eyebrow]" value="<?= si($s,'svc_faq_eyebrow') ?>" placeholder="Frequently Asked"></div>
      <div class="form-group"><label>Button Text</label><input type="text" name="settings[svc_faq_btn]" value="<?= si($s,'svc_faq_btn') ?>" placeholder="Book a Free Consultation"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Heading (before emphasis)</label><input type="text" name="settings[svc_faq_h2_pre]" value="<?= si($s,'svc_faq_h2_pre') ?>" placeholder="Services,"></div>
      <div class="form-group"><label>Heading (italic accent)</label><input type="text" name="settings[svc_faq_h2_em]" value="<?= si($s,'svc_faq_h2_em') ?>" placeholder="answered"></div>
    </div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[svc_faq_subtext]" rows="2"><?= si($s,'svc_faq_subtext') ?></textarea></div>
    <?php for($i=1;$i<=4;$i++): ?>
    <div style="border-top:1px solid #1e293b;padding-top:14px;margin-top:14px;">
      <div class="form-group"><label>Question <?= $i ?></label><input type="text" name="settings[svc_faq_<?= $i ?>_q]" value="<?= si($s,"svc_faq_{$i}_q") ?>"></div>
      <div class="form-group"><label>Answer <?= $i ?></label><textarea name="settings[svc_faq_<?= $i ?>_a]" rows="2"><?= si($s,"svc_faq_{$i}_a") ?></textarea></div>
    </div>
    <?php endfor; ?>
  </div>
</div>

<!-- SOLUTIONS PAGE -->
<div class="tab-panel" id="tab-solutions" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-boxes-stacked text-green" style="margin-right:8px;"></i>Hero Section</span></div>
    <div class="form-group"><label>Label</label><input type="text" name="settings[sol_hero_label]" value="<?= si($s,'sol_hero_label') ?>" placeholder="Solutions"></div>
    <div class="form-group"><label>Headline</label><input type="text" name="settings[sol_hero_h1]" value="<?= si($s,'sol_hero_h1') ?>" placeholder="Tailored Packages for Every Stage of Growth"></div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[sol_hero_subtext]" rows="2"><?= si($s,'sol_hero_subtext') ?></textarea></div>
  </div>
  <?php
  $pkgDefaults = [
    1=>['Starter','From $1,200','Perfect for small businesses ready to establish their digital presence and streamline core operations.','Professional business website, Basic CRM setup, WhatsApp Business integration, 1 automation workflow, Google My Business setup, 30-day support','Small businesses, sole traders, startups'],
    2=>['Growth','From $3,500','For growing businesses that need robust systems, an online presence, and automation to scale without adding headcount.','Custom business website or web app, Full CRM & inventory system, E-commerce store, Up to 5 automation workflows, Digital marketing setup, Staff training & onboarding, 60-day support','SMEs, retail, service businesses'],
    3=>['Enterprise','Custom pricing','End-to-end digital transformation for established businesses with complex operations and multiple locations.','Custom ERP or enterprise platform, Full automation infrastructure, Multi-location management, Advanced analytics & dashboards, Dedicated account manager, Ongoing development retainer, Priority support SLA','Multi-branch businesses, corporates'],
  ];
  foreach($pkgDefaults as $i=>$d): ?>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-box text-green" style="margin-right:8px;"></i>Package <?= $i ?><?= $i==2 ? ' (marked Most Popular)' : '' ?></span></div>
    <div class="form-row">
      <div class="form-group"><label>Name</label><input type="text" name="settings[sol_pkg_<?= $i ?>_name]" value="<?= si($s,"sol_pkg_{$i}_name") ?>" placeholder="<?= $d[0] ?>"></div>
      <div class="form-group"><label>Price</label><input type="text" name="settings[sol_pkg_<?= $i ?>_price]" value="<?= si($s,"sol_pkg_{$i}_price") ?>" placeholder="<?= $d[1] ?>"></div>
    </div>
    <div class="form-group"><label>Ideal For</label><input type="text" name="settings[sol_pkg_<?= $i ?>_ideal]" value="<?= si($s,"sol_pkg_{$i}_ideal") ?>" placeholder="<?= $d[5] ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="settings[sol_pkg_<?= $i ?>_desc]" rows="2"><?= si($s,"sol_pkg_{$i}_desc") ?></textarea></div>
    <div class="form-group"><label>What's Included (comma separated)</label><textarea name="settings[sol_pkg_<?= $i ?>_includes]" rows="3"><?= si($s,"sol_pkg_{$i}_includes") ?></textarea><small style="color:#64748b;">e.g. <?= htmlspecialchars($d[3]) ?></small></div>
  </div>
  <?php endforeach; ?>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-circle-question text-green" style="margin-right:8px;"></i>"Not Sure Which Package" Callout</span></div>
    <div class="form-group"><label>Heading</label><input type="text" name="settings[sol_callout_h3]" value="<?= si($s,'sol_callout_h3') ?>" placeholder="Not sure which package is right for you?"></div>
    <div class="form-group"><label>Subtext</label><input type="text" name="settings[sol_callout_desc]" value="<?= si($s,'sol_callout_desc') ?>"></div>
    <div class="form-group"><label>Button Text</label><input type="text" name="settings[sol_callout_btn]" value="<?= si($s,'sol_callout_btn') ?>" placeholder="Book a Free Consultation"></div>
  </div>
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-thumbs-up text-green" style="margin-right:8px;"></i>Why Businesses Choose Us</span></div>
    <div class="form-row">
      <div class="form-group"><label>Eyebrow Tag</label><input type="text" name="settings[sol_why_eyebrow]" value="<?= si($s,'sol_why_eyebrow') ?>" placeholder="Why Tedmark"></div>
      <div class="form-group"><label>Heading</label><input type="text" name="settings[sol_why_h2]" value="<?= si($s,'sol_why_h2') ?>" placeholder="Why Businesses Choose Us"></div>
    </div>
    <?php
    $solWhyDefaults = [1=>['Local Context','We understand local payment systems, infrastructure, and business culture, no guesswork.'],2=>['No Lock-in','You own everything we build. Full source code, full data. No vendor lock-in.'],3=>['Fast Delivery','Most projects launched within 4-8 weeks. We move fast without cutting corners.'],4=>['Local Support','Dedicated support in your timezone. Real people who know your system.']];
    foreach($solWhyDefaults as $i=>$d): ?>
    <div class="form-row" style="border-top:1px solid #1e293b;padding-top:14px;margin-top:14px;">
      <div class="form-group"><label>Item <?= $i ?> Title</label><input type="text" name="settings[sol_why_<?= $i ?>_title]" value="<?= si($s,"sol_why_{$i}_title") ?>" placeholder="<?= $d[0] ?>"></div>
      <div class="form-group"><label>Item <?= $i ?> Description</label><input type="text" name="settings[sol_why_<?= $i ?>_desc]" value="<?= si($s,"sol_why_{$i}_desc") ?>" placeholder="<?= $d[1] ?>"></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- CONSULTATION PAGE -->
<div class="tab-panel" id="tab-consultation" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-calendar-check text-green" style="margin-right:8px;"></i>Hero Section</span></div>
    <div class="form-row">
      <div class="form-group"><label>Headline (before emphasis)</label><input type="text" name="settings[cons_hero_h1_pre]" value="<?= si($s,'cons_hero_h1_pre') ?>" placeholder="Let's Map Out Your"></div>
      <div class="form-group"><label>Headline Emphasis (italic)</label><input type="text" name="settings[cons_hero_h1_em]" value="<?= si($s,'cons_hero_h1_em') ?>" placeholder="Digital Transformation"></div>
    </div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[cons_hero_subtext]" rows="2"><?= si($s,'cons_hero_subtext') ?></textarea></div>
  </div>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-list-ol text-green" style="margin-right:8px;"></i>3-Step Explainer</span></div>
    <?php
    $consStepDefaults = [1=>['1. You Book','Fill in the short form below. Takes under 2 minutes.'],2=>['2. We Call You','We confirm a time within 2 hours and call at your convenience.'],3=>['3. Get Your Roadmap','Walk away with a clear, prioritised plan, yours to keep either way.']];
    foreach($consStepDefaults as $i=>$d): ?>
    <div class="form-row" style="border-top:1px solid #1e293b;padding-top:14px;margin-top:14px;">
      <div class="form-group"><label>Step <?= $i ?> Title</label><input type="text" name="settings[cons_step_<?= $i ?>_title]" value="<?= si($s,"cons_step_{$i}_title") ?>" placeholder="<?= $d[0] ?>"></div>
      <div class="form-group"><label>Step <?= $i ?> Description</label><input type="text" name="settings[cons_step_<?= $i ?>_desc]" value="<?= si($s,"cons_step_{$i}_desc") ?>" placeholder="<?= $d[1] ?>"></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-quote-left text-green" style="margin-right:8px;"></i>Testimonial Strip</span></div>
    <div class="form-group"><label>Quote</label><textarea name="settings[cons_testimonial_quote]" rows="2"><?= si($s,'cons_testimonial_quote') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Name</label><input type="text" name="settings[cons_testimonial_name]" value="<?= si($s,'cons_testimonial_name') ?>" placeholder="Ama Boateng"></div>
      <div class="form-group"><label>Role / Company</label><input type="text" name="settings[cons_testimonial_role]" value="<?= si($s,'cons_testimonial_role') ?>" placeholder="Founder, StyleHouse GH"></div>
    </div>
  </div>
</div>

<!-- TOOLS PAGES -->
<div class="tab-panel" id="tab-tools" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-heart-pulse text-green" style="margin-right:8px;"></i>Business Health Checker</span></div>
    <div class="form-group"><label>Headline</label><input type="text" name="settings[tool_health_h1]" value="<?= si($s,'tool_health_h1') ?>" placeholder="Business Health Checker"></div>
    <div class="form-group"><label>Subtext</label><input type="text" name="settings[tool_health_subtext]" value="<?= si($s,'tool_health_subtext') ?>" placeholder="10 questions. 3 minutes. Get a detailed score of how your business is performing and where to improve."></div>
  </div>
  <div class="tm-card" style="margin-bottom:20px;">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-calculator text-green" style="margin-right:8px;"></i>ROI Calculator</span></div>
    <div class="form-group"><label>Headline</label><input type="text" name="settings[tool_roi_h1]" value="<?= si($s,'tool_roi_h1') ?>" placeholder="Calculate Your Hidden Losses"></div>
    <div class="form-group"><label>Subtext</label><input type="text" name="settings[tool_roi_subtext]" value="<?= si($s,'tool_roi_subtext') ?>" placeholder="Find out exactly how much your manual processes are costing you, and what automation would save."></div>
  </div>
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-wand-magic-sparkles text-green" style="margin-right:8px;"></i>Solution Recommender</span></div>
    <div class="form-group"><label>Headline</label><input type="text" name="settings[tool_reco_h1]" value="<?= si($s,'tool_reco_h1') ?>" placeholder="Find Your Perfect Solution"></div>
    <div class="form-group"><label>Subtext</label><input type="text" name="settings[tool_reco_subtext]" value="<?= si($s,'tool_reco_subtext') ?>"></div>
    <p style="color:#64748b;font-size:0.8rem;margin-top:10px;">Only the hero headline/subtext is editable here. The questions, scoring, and recommendation logic are intentionally left in code since editing them safely requires matching logic changes, not just text.</p>
  </div>
</div>

<!-- CTA -->
<div class="tab-panel" id="tab-cta" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-bullhorn text-green" style="margin-right:8px;"></i>CTA Band (shown on all pages)</span></div>
    <div class="form-group"><label>Heading</label><input type="text" name="settings[cta_heading]" value="<?= si($s,'cta_heading') ?>"></div>
    <div class="form-group"><label>Subtext</label><textarea name="settings[cta_subtext]" rows="2"><?= si($s,'cta_subtext') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Primary Button Text</label><input type="text" name="settings[cta_btn_primary]" value="<?= si($s,'cta_btn_primary') ?>"></div>
      <div class="form-group"><label>Secondary Button Text</label><input type="text" name="settings[cta_btn_secondary]" value="<?= si($s,'cta_btn_secondary') ?>"></div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<div class="tab-panel" id="tab-footer" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-shoe-prints text-green" style="margin-right:8px;"></i>Footer Content</span></div>
    <div class="form-group"><label>Footer Tagline</label><textarea name="settings[footer_tagline]" rows="2"><?= si($s,'footer_tagline') ?></textarea></div>
  </div>
</div>

<!-- SOCIAL -->
<div class="tab-panel" id="tab-social" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-share-nodes text-green" style="margin-right:8px;"></i>Social Media Links</span></div>
    <div class="form-row">
      <div class="form-group"><label><i class="fa-brands fa-twitter" style="margin-right:6px;"></i>Twitter / X</label><input type="text" name="settings[social_twitter]" value="<?= si($s,'social_twitter') ?>" placeholder="https://twitter.com/..."></div>
      <div class="form-group"><label><i class="fa-brands fa-linkedin" style="margin-right:6px;"></i>LinkedIn</label><input type="text" name="settings[social_linkedin]" value="<?= si($s,'social_linkedin') ?>" placeholder="https://linkedin.com/..."></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><i class="fa-brands fa-instagram" style="margin-right:6px;"></i>Instagram</label><input type="text" name="settings[social_instagram]" value="<?= si($s,'social_instagram') ?>" placeholder="https://instagram.com/..."></div>
      <div class="form-group"><label><i class="fa-brands fa-facebook" style="margin-right:6px;"></i>Facebook</label><input type="text" name="settings[social_facebook]" value="<?= si($s,'social_facebook') ?>" placeholder="https://facebook.com/..."></div>
    </div>
  </div>
</div>

<!-- SAVE BUTTON -->
<div style="position:sticky;bottom:0;background:var(--bg);padding:16px 0;margin-top:24px;border-top:1px solid #1e293b;">
  <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save All Settings</button>
</div>

</form>

<script>
const tabs = document.querySelectorAll('.tab-btn');
const panels = document.querySelectorAll('.tab-panel');
tabs.forEach(btn => {
  btn.addEventListener('click', () => {
    tabs.forEach(t => { t.style.borderBottomColor='transparent'; t.style.color='#64748b'; });
    panels.forEach(p => p.style.display='none');
    btn.style.borderBottomColor='#22c55e';
    btn.style.color='#22c55e';
    document.getElementById('tab-'+btn.dataset.tab).style.display='block';
  });
});
tabs[0].click();
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
