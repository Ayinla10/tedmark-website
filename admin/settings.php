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
  <?php foreach(['general'=>'General','homepage'=>'Homepage','cta'=>'CTA Band','footer'=>'Footer','social'=>'Social Media'] as $tab=>$label): ?>
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
  <div class="tm-card">
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
      <div class="form-group"><label><i class="fa-brands fa-twitter" style="margin-right:6px;"></i>Twitter / X</label><input type="url" name="settings[social_twitter]" value="<?= si($s,'social_twitter') ?>" placeholder="https://twitter.com/..."></div>
      <div class="form-group"><label><i class="fa-brands fa-linkedin" style="margin-right:6px;"></i>LinkedIn</label><input type="url" name="settings[social_linkedin]" value="<?= si($s,'social_linkedin') ?>" placeholder="https://linkedin.com/..."></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><i class="fa-brands fa-instagram" style="margin-right:6px;"></i>Instagram</label><input type="url" name="settings[social_instagram]" value="<?= si($s,'social_instagram') ?>" placeholder="https://instagram.com/..."></div>
      <div class="form-group"><label><i class="fa-brands fa-facebook" style="margin-right:6px;"></i>Facebook</label><input type="url" name="settings[social_facebook]" value="<?= si($s,'social_facebook') ?>" placeholder="https://facebook.com/..."></div>
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
