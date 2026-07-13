<?php
$pageTitle = 'Edit Project';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$proj = $id ? fetchOne("SELECT * FROM projects WHERE id = ?", [$id]) : null;
$pageTitle = $proj ? 'Edit Project' : 'New Project';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $client      = trim($_POST['client'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $tags        = trim($_POST['tags'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $challenge   = trim($_POST['challenge'] ?? '');
    $solution    = trim($_POST['solution'] ?? '');
    $result      = trim($_POST['result'] ?? '');
    $icon        = trim($_POST['icon'] ?? 'fa-solid fa-briefcase');
    $color       = trim($_POST['color'] ?? '#22c55e');
    $bg          = trim($_POST['bg'] ?? 'linear-gradient(135deg,#0f172a,#1e3a5f)');
    $cover       = trim($_POST['cover_image'] ?? '');
    $year        = trim($_POST['year'] ?? date('Y'));
    $status      = $_POST['status'] ?? 'active';
    $sort        = (int)($_POST['sort_order'] ?? 0);

    if (!$title) { $error = 'Title is required.'; }
    else {
        if (!$slug) $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $data = ['title'=>$title,'slug'=>$slug,'client'=>$client,'location'=>$location,'category'=>$category,'tags'=>$tags,'description'=>$description,'challenge'=>$challenge,'solution'=>$solution,'result'=>$result,'icon'=>$icon,'color'=>$color,'bg'=>$bg,'cover_image'=>$cover,'year'=>$year,'status'=>$status,'sort_order'=>$sort];
        try {
            if ($proj) {
                $set = implode(', ', array_map(fn($k) => "`$k`=?", array_keys($data)));
                query("UPDATE projects SET $set WHERE id = ?", [...array_values($data), $proj['id']]);
                $id = $proj['id'];
            } else {
                $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($data)));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                query("INSERT INTO projects ($cols) VALUES ($ph)", array_values($data));
                $id = db()->lastInsertId();
            }
            header('Location: ' . SITE_URL . '/admin/project-edit.php?id=' . $id . '&msg=saved'); exit;
        } catch (Exception $e) { $error = 'Error saving: ' . $e->getMessage(); }
    }
}

$msg = $_GET['msg'] ?? '';
$p = $proj ?? [];
require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($msg==='saved'): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> Project saved!</div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

  <div style="display:flex;flex-direction:column;gap:20px;">
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Project Details</span></div>
      <div class="form-row">
        <div class="form-group"><label>Project Title *</label><input type="text" name="title" id="title-input" value="<?= htmlspecialchars($p['title']??'') ?>" required></div>
        <div class="form-group"><label>Client Name</label><input type="text" name="client" value="<?= htmlspecialchars($p['client']??'') ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Client Location</label><input type="text" name="location" value="<?= htmlspecialchars($p['location']??'') ?>" placeholder="e.g. Accra, Ghana or London, UK"></div>
        <div class="form-group"><label>Year</label><input type="text" name="year" value="<?= htmlspecialchars($p['year']??date('Y')) ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Slug</label><input type="text" name="slug" id="slug-input" value="<?= htmlspecialchars($p['slug']??'') ?>"></div>
        <div></div>
      </div>
      <div class="form-group"><label>Short Description</label><textarea name="description" rows="3"><?= htmlspecialchars($p['description']??'') ?></textarea></div>
      <div class="form-group"><label>Result Badge (e.g. +340% online sales)</label><input type="text" name="result" value="<?= htmlspecialchars($p['result']??'') ?>" placeholder="+340% online sales"></div>
    </div>

    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Full Case Study</span></div>
      <div class="form-group"><label>The Challenge</label><textarea name="challenge" rows="4"><?= htmlspecialchars($p['challenge']??'') ?></textarea></div>
      <div class="form-group"><label>Our Solution</label><textarea name="solution" rows="4"><?= htmlspecialchars($p['solution']??'') ?></textarea></div>
    </div>

    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Visual Style</span></div>
      <div class="form-row-3">
        <div class="form-group"><label>Icon (FA class)</label><input type="text" name="icon" value="<?= htmlspecialchars($p['icon']??'fa-solid fa-briefcase') ?>" placeholder="fa-solid fa-globe"></div>
        <div class="form-group"><label>Icon Color</label><input type="color" name="color" value="<?= htmlspecialchars($p['color']??'#22c55e') ?>" style="height:42px;padding:4px;cursor:pointer;"></div>
        <div class="form-group"><label>Cover Image URL</label><input type="url" name="cover_image" value="<?= htmlspecialchars($p['cover_image']??'') ?>"></div>
      </div>
      <div class="form-group"><label>Card Background (CSS gradient)</label><input type="text" name="bg" value="<?= htmlspecialchars($p['bg']??'linear-gradient(135deg,#0f172a,#1e3a5f)') ?>"></div>
    </div>
  </div>

  <!-- Sidebar -->
  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px;">
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Publish</span></div>
      <div class="form-group"><label>Status</label>
        <select name="status">
          <option value="active" <?= ($p['status']??'')==='active'?'selected':'' ?>>Active</option>
          <option value="draft" <?= ($p['status']??'')==='draft'?'selected':'' ?>>Draft</option>
        </select>
      </div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= htmlspecialchars($p['sort_order']??'0') ?>" min="0"></div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Save Project</button>
    </div>
    <div class="tm-card">
      <div class="form-group"><label>Category</label>
        <select name="category">
          <?php foreach(['web'=>'Web','systems'=>'Systems','ecommerce'=>'E-Commerce','branding'=>'Branding','automation'=>'Automation'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($p['category']??'')===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Tags (comma separated)</label><input type="text" name="tags" value="<?= htmlspecialchars($p['tags']??'') ?>"></div>
    </div>
    <a href="<?= SITE_URL ?>/admin/projects.php" class="btn btn-ghost" style="justify-content:center;"><i class="fa-solid fa-arrow-left"></i> All Projects</a>
  </div>
</div>
</form>

<script>
const t = document.getElementById('title-input');
const s = document.getElementById('slug-input');
t.addEventListener('input', () => {
  if (!s.dataset.manual) s.value = t.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
});
s.addEventListener('input', () => s.dataset.manual = '1');
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
