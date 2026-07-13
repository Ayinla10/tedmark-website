<?php
$pageTitle = 'Homepage Editor';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$success = '';

// Load all settings
$rows = fetchAll("SELECT `key`, `value` FROM settings");
$s = array_column($rows, 'value', 'key');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = $_POST['settings'] ?? [];
    foreach ($fields as $key => $val) {
        $key = preg_replace('/[^a-z0-9_]/', '', $key);
        $existing = fetchOne("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($existing) {
            query("UPDATE settings SET `value` = ? WHERE `key` = ?", [trim($val), $key]);
        } else {
            query("INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, 'homepage')", [$key, trim($val)]);
        }
    }
    header('Location: ' . SITE_URL . '/admin/homepage.php?saved=1'); exit;
}

$success = isset($_GET['saved']) ? 'Homepage content updated!' : '';

require_once __DIR__ . '/includes/admin-layout.php';

function sv($s, $k, $d='') { return htmlspecialchars($s[$k] ?? $d); }
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div><?php endif; ?>

<form method="POST" action="<?= SITE_URL ?>/admin/homepage.php">

<!-- Tab nav -->
<div style="display:flex;gap:4px;margin-bottom:24px;background:#0b1528;padding:4px;border-radius:10px;width:fit-content;">
  <?php $tabs=[['hero','fa-image','Hero'],['about','fa-circle-info','About'],['stats','fa-chart-bar','Stats'],['services','fa-briefcase','Services'],['cta','fa-bullhorn','CTA']]; ?>
  <?php foreach($tabs as [$tid,$tic,$tlabel]): ?>
  <button type="button" class="tab-btn" data-tab="<?= $tid ?>" style="padding:7px 16px;border:none;border-radius:7px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all .15s;background:transparent;color:#64748b;">
    <i class="fa-solid <?= $tic ?>" style="margin-right:5px;"></i><?= $tlabel ?>
  </button>
  <?php endforeach; ?>
</div>

<!-- HERO TAB -->
<div class="tab-panel" id="tab-hero">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-image" style="color:#22c55e;margin-right:8px;"></i>Hero Background</span></div>
      <div class="form-group">
        <label>Background Image URL</label>
        <input type="text" name="settings[hero_bg_image]" value="<?= sv($s,'hero_bg_image') ?>" placeholder="https://images.unsplash.com/...">
        <small style="color:#64748b;font-size:0.75rem;">Use direct image CDN URLs (not page URLs). Recommended: images.unsplash.com/photo-ID?w=1600&q=80</small>
      </div>
      <div class="form-group">
        <label>Overlay Opacity <span style="color:#64748b;font-weight:400;">(0.0 = transparent → 1.0 = fully dark)</span></label>
        <input type="text" name="settings[hero_overlay_opacity]" value="<?= sv($s,'hero_overlay_opacity','0.92') ?>" placeholder="0.92">
      </div>
      <?php if (!empty($s['hero_bg_image'])): ?>
      <div style="margin-top:4px;border-radius:8px;overflow:hidden;max-height:120px;">
        <img src="<?= htmlspecialchars($s['hero_bg_image']) ?>" style="width:100%;height:120px;object-fit:cover;display:block;" onerror="this.style.display='none'">
      </div>
      <?php endif; ?>
    </div>

    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-tag" style="color:#f59e0b;margin-right:8px;"></i>Hero Badge & Headline</span></div>
      <div class="form-group">
        <label>Badge Text</label>
        <input type="text" name="settings[hero_badge]" value="<?= sv($s,'hero_badge') ?>" placeholder="🚀 Trusted by 200+ businesses">
      </div>
      <div class="form-group">
        <label>Headline Line 1</label>
        <input type="text" name="settings[hero_h1_line1]" value="<?= sv($s,'hero_h1_line1') ?>" placeholder="Transform Your">
      </div>
      <div class="form-group">
        <label>Headline Line 2 <span style="color:#22c55e;font-size:0.75rem;">(accent color)</span></label>
        <input type="text" name="settings[hero_h1_line2]" value="<?= sv($s,'hero_h1_line2') ?>" placeholder="Business With">
      </div>
      <div class="form-group">
        <label>Headline Line 3</label>
        <input type="text" name="settings[hero_h1_line3]" value="<?= sv($s,'hero_h1_line3') ?>" placeholder="Smart Technology">
      </div>
    </div>

    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-align-left" style="color:#818cf8;margin-right:8px;"></i>Subtext & Buttons</span></div>
      <div class="form-group">
        <label>Subtitle / Subtext</label>
        <textarea name="settings[hero_subtext]" rows="3" placeholder="We help businesses..."><?= sv($s,'hero_subtext') ?></textarea>
      </div>
      <div class="form-group">
        <label>Primary Button Text</label>
        <input type="text" name="settings[hero_btn1_text]" value="<?= sv($s,'hero_btn1_text','Get Free Consultation') ?>">
      </div>
      <div class="form-group">
        <label>Secondary Button Text</label>
        <input type="text" name="settings[hero_btn2_text]" value="<?= sv($s,'hero_btn2_text','See Our Work') ?>">
      </div>
    </div>

    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-hashtag" style="color:#f59e0b;margin-right:8px;"></i>Hero Stats</span></div>
      <div class="form-group"><label>Stat 1 Number</label><input type="text" name="settings[stat1_number]" value="<?= sv($s,'stat1_number','200+') ?>"></div>
      <div class="form-group"><label>Stat 1 Label</label><input type="text" name="settings[stat1_label]" value="<?= sv($s,'stat1_label','Businesses Served') ?>"></div>
      <div class="form-group"><label>Stat 2 Number</label><input type="text" name="settings[stat2_number]" value="<?= sv($s,'stat2_number','98%') ?>"></div>
      <div class="form-group"><label>Stat 2 Label</label><input type="text" name="settings[stat2_label]" value="<?= sv($s,'stat2_label','Client Satisfaction') ?>"></div>
      <div class="form-group"><label>Stat 3 Number</label><input type="text" name="settings[stat3_number]" value="<?= sv($s,'stat3_number','8+') ?>"></div>
      <div class="form-group"><label>Stat 3 Label</label><input type="text" name="settings[stat3_label]" value="<?= sv($s,'stat3_label','Years Experience') ?>"></div>
    </div>
  </div>
</div>

<!-- ABOUT TAB -->
<div class="tab-panel" id="tab-about" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-circle-info" style="color:#22c55e;margin-right:8px;"></i>About Section (Homepage)</span></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div class="form-group"><label>Section Badge</label><input type="text" name="settings[about_badge]" value="<?= sv($s,'about_badge','About Tedmark') ?>"></div>
      <div class="form-group"><label>Section Heading</label><input type="text" name="settings[about_heading]" value="<?= sv($s,'about_heading') ?>" placeholder="We Build Digital Systems..."></div>
    </div>
    <div class="form-group"><label>About Description (Paragraph 1)</label><textarea name="settings[about_p1]" rows="4"><?= sv($s,'about_p1') ?></textarea></div>
    <div class="form-group"><label>About Description (Paragraph 2)</label><textarea name="settings[about_p2]" rows="3"><?= sv($s,'about_p2') ?></textarea></div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
      <div class="form-group"><label>Feature 1</label><input type="text" name="settings[about_feat1]" value="<?= sv($s,'about_feat1','Custom Web Solutions') ?>"></div>
      <div class="form-group"><label>Feature 2</label><input type="text" name="settings[about_feat2]" value="<?= sv($s,'about_feat2','Business Automation') ?>"></div>
      <div class="form-group"><label>Feature 3</label><input type="text" name="settings[about_feat3]" value="<?= sv($s,'about_feat3','24/7 Support') ?>"></div>
    </div>
    <div class="form-group"><label>About Image URL</label><input type="text" name="settings[about_image]" value="<?= sv($s,'about_image') ?>" placeholder="https://..."></div>
  </div>
</div>

<!-- STATS TAB -->
<div class="tab-panel" id="tab-stats" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-chart-bar" style="color:#22c55e;margin-right:8px;"></i>Impact / Stats Strip</span></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <?php for($i=1;$i<=4;$i++): ?>
      <div style="background:#0b1528;padding:16px;border-radius:10px;border:1px solid #1e293b;">
        <p style="color:#64748b;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Stat <?= $i ?></p>
        <div class="form-group"><label>Number</label><input type="text" name="settings[impact_stat<?= $i ?>_num]" value="<?= sv($s,"impact_stat{$i}_num") ?>" placeholder="500+"></div>
        <div class="form-group"><label>Label</label><input type="text" name="settings[impact_stat<?= $i ?>_label]" value="<?= sv($s,"impact_stat{$i}_label") ?>" placeholder="Projects Done"></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<!-- SERVICES TAB -->
<div class="tab-panel" id="tab-services" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-briefcase" style="color:#22c55e;margin-right:8px;"></i>Services Section Header</span></div>
    <p style="color:#64748b;font-size:0.83rem;margin-bottom:16px;">To manage the actual services (icons, descriptions), go to <a href="services.php" style="color:#22c55e;">Services →</a></p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div class="form-group"><label>Section Badge</label><input type="text" name="settings[services_badge]" value="<?= sv($s,'services_badge','What We Do') ?>"></div>
      <div class="form-group"><label>Section Heading</label><input type="text" name="settings[services_heading]" value="<?= sv($s,'services_heading','Solutions Built for Growth') ?>"></div>
    </div>
    <div class="form-group"><label>Section Subtext</label><textarea name="settings[services_subtext]" rows="2"><?= sv($s,'services_subtext') ?></textarea></div>
  </div>
</div>

<!-- CTA TAB -->
<div class="tab-panel" id="tab-cta" style="display:none;">
  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-bullhorn" style="color:#f59e0b;margin-right:8px;"></i>CTA Band (Bottom of Homepage)</span></div>
    <p style="color:#64748b;font-size:0.83rem;margin-bottom:16px;">This band also appears on other pages. Edit it here or in <a href="settings.php?tab=cta" style="color:#22c55e;">Settings → CTA Band</a>.</p>
    <div class="form-group"><label>CTA Heading</label><input type="text" name="settings[cta_heading]" value="<?= sv($s,'cta_heading') ?>" placeholder="Ready to Transform Your Business?"></div>
    <div class="form-group"><label>CTA Subtext</label><textarea name="settings[cta_subtext]" rows="2"><?= sv($s,'cta_subtext') ?></textarea></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div class="form-group"><label>Button 1 Text</label><input type="text" name="settings[cta_btn1_text]" value="<?= sv($s,'cta_btn1_text','Get Free Consultation') ?>"></div>
      <div class="form-group"><label>Button 2 Text</label><input type="text" name="settings[cta_btn2_text]" value="<?= sv($s,'cta_btn2_text','View Our Work') ?>"></div>
    </div>
  </div>
</div>

<!-- Save bar -->
<div style="position:sticky;bottom:0;background:#0b1528;border-top:1px solid #1e293b;padding:16px 0;margin-top:24px;z-index:40;">
  <button type="submit" class="btn btn-primary" style="min-width:200px;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Save Homepage Content</button>
  <a href="<?= SITE_URL ?>/" target="_blank" class="btn btn-ghost btn-sm" style="margin-left:12px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Preview Homepage</a>
</div>

</form>

<script>
const tabBtns = document.querySelectorAll('.tab-btn');
const tabPanels = document.querySelectorAll('.tab-panel');
function showTab(id) {
  tabPanels.forEach(p => p.style.display = p.id === 'tab-'+id ? '' : 'none');
  tabBtns.forEach(b => {
    const active = b.dataset.tab === id;
    b.style.background = active ? 'var(--card)' : 'transparent';
    b.style.color = active ? '#fff' : '#64748b';
  });
}
tabBtns.forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
showTab('hero');
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
