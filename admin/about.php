<?php
$pageTitle = 'About Page Editor';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = $_POST['settings'] ?? [];
    foreach ($fields as $key => $val) {
        $key = preg_replace('/[^a-z0-9_]/', '', $key);
        $existing = fetchOne("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($existing) {
            query("UPDATE settings SET `value` = ? WHERE `key` = ?", [trim($val), $key]);
        } else {
            query("INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, 'about')", [$key, trim($val)]);
        }
    }
    header('Location: ' . SITE_URL . '/admin/about.php?saved=1'); exit;
}

$rows = fetchAll("SELECT `key`, `value` FROM settings");
$s    = array_column($rows, 'value', 'key');
$success = isset($_GET['saved']) ? 'About page updated successfully!' : '';

require_once __DIR__ . '/includes/admin-layout.php';
function sv($s, $k, $d='') { return htmlspecialchars($s[$k] ?? $d); }
?>

<?php if ($success): ?>
<div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div>
<?php endif; ?>

<form method="POST" action="<?= SITE_URL ?>/admin/about.php">

<!-- Tab nav -->
<div style="display:flex;gap:4px;margin-bottom:24px;background:#0b1528;padding:4px;border-radius:10px;width:fit-content;">
  <?php $tabs=[['hero','fa-image','Hero'],['story','fa-book-open','Story'],['stats','fa-chart-bar','Stats'],['values','fa-star','Values'],['team','fa-users','Team']]; ?>
  <?php foreach($tabs as $i=>[$id,$ic,$lbl]): ?>
  <button type="button" onclick="showTab('<?= $id ?>')" id="tab-<?= $id ?>"
    style="padding:8px 18px;border-radius:8px;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;transition:all .15s;<?= $i===0?'background:#22c55e;color:#fff;':'background:transparent;color:#64748b;' ?>">
    <i class="fa-solid <?= $ic ?> fa-xs"></i> <?= $lbl ?>
  </button>
  <?php endforeach; ?>
</div>

<!-- ── HERO TAB ── -->
<div id="panel-hero">
  <div class="tm-card" style="margin-bottom:20px;">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:18px;"><i class="fa-solid fa-image fa-sm" style="color:#22c55e;margin-right:8px;"></i>Hero Section</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div style="grid-column:1/-1;">
        <label class="tm-form-label">Main Heading</label>
        <input type="text" name="settings[about_hero_heading]" class="tm-form-input" value="<?= sv($s,'about_hero_heading','We Help Businesses Run Smarter') ?>">
      </div>
      <div style="grid-column:1/-1;">
        <label class="tm-form-label">Hero Subtext</label>
        <textarea name="settings[about_hero_subtext]" class="tm-form-input tm-form-textarea" rows="3"><?= sv($s,'about_hero_subtext','Tedmark Digital Agency was founded with one purpose: to give growing businesses access to the same quality of technology, automation, and digital infrastructure that industry leaders rely on every day.') ?></textarea>
      </div>
      <div>
        <label class="tm-form-label">Mission Statement</label>
        <textarea name="settings[about_mission]" class="tm-form-input tm-form-textarea" rows="3"><?= sv($s,'about_mission','To make enterprise-grade technology accessible to every business — regardless of size, sector, or location.') ?></textarea>
      </div>
      <div>
        <label class="tm-form-label">Hero Background Image URL</label>
        <input type="url" name="settings[about_hero_image]" class="tm-form-input" placeholder="https://..." value="<?= sv($s,'about_hero_image') ?>">
        <small style="color:#64748b;font-size:0.75rem;">Leave blank to use default Unsplash image</small>
      </div>
    </div>
  </div>
</div>

<!-- ── STORY TAB ── -->
<div id="panel-story" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:18px;"><i class="fa-solid fa-book-open fa-sm" style="color:#22c55e;margin-right:8px;"></i>Our Story Section</h3>
    <div style="display:grid;gap:16px;">
      <div>
        <label class="tm-form-label">Section Label</label>
        <input type="text" name="settings[about_story_label]" class="tm-form-input" value="<?= sv($s,'about_story_label','Our Story') ?>">
      </div>
      <div>
        <label class="tm-form-label">Section Heading</label>
        <input type="text" name="settings[about_story_heading]" class="tm-form-input" value="<?= sv($s,'about_story_heading','Built From Real Frustration') ?>">
      </div>
      <div>
        <label class="tm-form-label">Paragraph 1</label>
        <textarea name="settings[about_story_p1]" class="tm-form-input tm-form-textarea" rows="4"><?= sv($s,'about_story_p1','We started Tedmark Digital after watching talented business owners lose time, money, and customers because they lacked the right systems. Manual invoicing, lost customer data, no inventory visibility — problems that technology solved elsewhere decades ago.') ?></textarea>
      </div>
      <div>
        <label class="tm-form-label">Paragraph 2</label>
        <textarea name="settings[about_story_p2]" class="tm-form-input tm-form-textarea" rows="4"><?= sv($s,'about_story_p2','We decided to close that gap. We\'ve since helped over 80 businesses across Ghana, Nigeria, Kenya, and beyond transform their operations with custom technology that fits their exact context and budget.') ?></textarea>
      </div>
      <div>
        <label class="tm-form-label">Paragraph 3</label>
        <textarea name="settings[about_story_p3]" class="tm-form-input tm-form-textarea" rows="3"><?= sv($s,'about_story_p3','We don\'t sell generic software. We build what each business actually needs — and we stay to make sure it works.') ?></textarea>
      </div>
    </div>
  </div>
</div>

<!-- ── STATS TAB ── -->
<div id="panel-stats" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:6px;"><i class="fa-solid fa-chart-bar fa-sm" style="color:#22c55e;margin-right:8px;"></i>Stats / Numbers</h3>
    <p style="font-size:0.82rem;color:#64748b;margin-bottom:20px;">These stats appear in the About hero and across the site.</p>
    <?php for($i=1;$i<=4;$i++): ?>
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #1e293b;">
      <div>
        <label class="tm-form-label">Stat <?= $i ?> Value</label>
        <input type="text" name="settings[stat_<?= $i ?>_value]" class="tm-form-input" value="<?= sv($s,"stat_{$i}_value") ?>" placeholder="e.g. 80+">
      </div>
      <div>
        <label class="tm-form-label">Stat <?= $i ?> Label</label>
        <input type="text" name="settings[stat_<?= $i ?>_label]" class="tm-form-input" value="<?= sv($s,"stat_{$i}_label") ?>" placeholder="e.g. Projects Delivered">
      </div>
    </div>
    <?php endfor; ?>
  </div>
</div>

<!-- ── VALUES TAB ── -->
<div id="panel-values" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:6px;"><i class="fa-solid fa-star fa-sm" style="color:#22c55e;margin-right:8px;"></i>Our Values</h3>
    <p style="font-size:0.82rem;color:#64748b;margin-bottom:20px;">4 value cards displayed in the Story section. Use Font Awesome class names for icons.</p>
    <?php
    $valDefaults = [
      1=>['icon'=>'fa-solid fa-bullseye','color'=>'#22c55e','title'=>'Results-first','desc'=>'We measure success by the impact on your business, not hours billed.'],
      2=>['icon'=>'fa-solid fa-handshake','color'=>'#60a5fa','title'=>'Long-term Partners','desc'=>"We build relationships, not transactions. We're here for your growth journey."],
      3=>['icon'=>'fa-solid fa-lock','color'=>'#a78bfa','title'=>'Transparency','desc'=>'Fixed pricing. No surprises. You always know what you\'re getting.'],
      4=>['icon'=>'fa-solid fa-leaf','color'=>'#f59e0b','title'=>'Context-first','desc'=>'We design for the realities of where you operate — local payments, infrastructure, and context.'],
    ];
    foreach($valDefaults as $i=>$def): ?>
    <div style="padding:16px;background:#0b1528;border-radius:10px;margin-bottom:12px;">
      <div style="font-size:0.75rem;font-weight:700;color:#22c55e;margin-bottom:10px;">Value <?= $i ?></div>
      <div style="display:grid;grid-template-columns:2fr 1fr 3fr;gap:12px;">
        <div>
          <label class="tm-form-label">Title</label>
          <input type="text" name="settings[about_value_<?= $i ?>_title]" class="tm-form-input" value="<?= sv($s,"about_value_{$i}_title",$def['title']) ?>">
        </div>
        <div>
          <label class="tm-form-label">Icon Class</label>
          <input type="text" name="settings[about_value_<?= $i ?>_icon]" class="tm-form-input" value="<?= sv($s,"about_value_{$i}_icon",$def['icon']) ?>" placeholder="fa-solid fa-star">
        </div>
        <div>
          <label class="tm-form-label">Description</label>
          <input type="text" name="settings[about_value_<?= $i ?>_desc]" class="tm-form-input" value="<?= sv($s,"about_value_{$i}_desc",$def['desc']) ?>">
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── TEAM TAB ── -->
<div id="panel-team" style="display:none;">
  <div class="tm-card" style="margin-bottom:20px;">
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:6px;"><i class="fa-solid fa-users fa-sm" style="color:#22c55e;margin-right:8px;"></i>Team Section</h3>
    <p style="font-size:0.82rem;color:#64748b;margin-bottom:20px;">Team members are managed in <a href="<?= SITE_URL ?>/admin/team.php" style="color:#22c55e;">Team Members</a>. Edit section header here.</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div>
        <label class="tm-form-label">Section Label</label>
        <input type="text" name="settings[about_team_label]" class="tm-form-input" value="<?= sv($s,'about_team_label','The Team') ?>">
      </div>
      <div>
        <label class="tm-form-label">Section Heading</label>
        <input type="text" name="settings[about_team_heading]" class="tm-form-input" value="<?= sv($s,'about_team_heading','The People Behind the Work') ?>">
      </div>
      <div style="grid-column:1/-1;">
        <label class="tm-form-label">Section Subtext</label>
        <input type="text" name="settings[about_team_subtext]" class="tm-form-input" value="<?= sv($s,'about_team_subtext','A lean, expert team with deep roots in technology and business.') ?>">
      </div>
    </div>
  </div>
  <!-- Quick team member table -->
  <?php
  try { $teamMembers = fetchAll("SELECT id, name, role, status FROM team_members ORDER BY sort_order ASC"); }
  catch(Exception $e) { $teamMembers = []; }
  ?>
  <?php if(!empty($teamMembers)): ?>
  <div class="tm-card">
    <h4 style="font-size:0.85rem;font-weight:700;color:#64748b;margin-bottom:14px;text-transform:uppercase;letter-spacing:.06em;">Current Team Members</h4>
    <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
      <thead>
        <tr style="border-bottom:1px solid #1e293b;">
          <th style="text-align:left;padding:8px 10px;color:#64748b;font-weight:600;">Name</th>
          <th style="text-align:left;padding:8px 10px;color:#64748b;font-weight:600;">Role</th>
          <th style="text-align:left;padding:8px 10px;color:#64748b;font-weight:600;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($teamMembers as $m): ?>
        <tr style="border-bottom:1px solid #1e293b;">
          <td style="padding:10px;color:#e2e8f0;"><?= htmlspecialchars($m['name']) ?></td>
          <td style="padding:10px;color:#94a3b8;"><?= htmlspecialchars($m['role']??'') ?></td>
          <td style="padding:10px;"><span style="font-size:0.72rem;padding:3px 8px;border-radius:4px;font-weight:700;<?= $m['status']==='active'?'background:rgba(34,197,94,0.1);color:#22c55e;':'background:rgba(100,116,139,0.1);color:#64748b;' ?>"><?= $m['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="margin-top:14px;">
      <a href="<?= SITE_URL ?>/admin/team.php" class="tm-btn-sm" style="background:#1e293b;color:#e2e8f0;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:0.82rem;font-weight:600;">
        <i class="fa-solid fa-users-gear fa-xs"></i> Manage Team Members
      </a>
    </div>
  </div>
  <?php else: ?>
  <div style="background:#0b1528;border-radius:10px;padding:24px;text-align:center;color:#64748b;font-size:0.875rem;">
    No team members added yet. <a href="<?= SITE_URL ?>/admin/team.php" style="color:#22c55e;">Add team members</a>
  </div>
  <?php endif; ?>
</div>

<!-- Save bar -->
<div style="position:sticky;bottom:0;background:#0f172a;border-top:1px solid #1e293b;padding:16px 0;margin-top:28px;display:flex;align-items:center;gap:12px;">
  <button type="submit" class="tm-btn-primary">
    <i class="fa-solid fa-floppy-disk fa-sm"></i> Save About Page
  </button>
  <a href="<?= SITE_URL ?>/about.php" target="_blank" style="font-size:0.82rem;color:#64748b;text-decoration:none;">
    <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i> Preview page
  </a>
</div>

</form>

<script>
function showTab(id) {
  ['hero','story','stats','values','team'].forEach(function(t){
    document.getElementById('panel-'+t).style.display = t===id?'':'none';
    var btn = document.getElementById('tab-'+t);
    btn.style.background = t===id?'#22c55e':'transparent';
    btn.style.color      = t===id?'#fff':'#64748b';
  });
}
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
