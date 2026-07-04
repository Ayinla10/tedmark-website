<?php
$pageTitle = 'Services';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$editing = null;
$success = $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM services WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/services.php'); exit;
}
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM services WHERE id = ?", [$_GET['edit']]);
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $svc = fetchOne("SELECT status FROM services WHERE id = ?", [$_GET['toggle']]);
    if ($svc) query("UPDATE services SET status=? WHERE id=?", [$svc['status']==='active'?'draft':'active', $_GET['toggle']]);
    header('Location: ' . SITE_URL . '/admin/services.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $icon        = trim($_POST['icon'] ?? 'fa-solid fa-star');
    $color       = trim($_POST['color'] ?? '#22c55e');
    $description = trim($_POST['description'] ?? '');
    $features    = trim($_POST['features'] ?? '');
    $status      = $_POST['status'] ?? 'active';
    $sort        = (int)($_POST['sort_order'] ?? 0);
    $editId      = (int)($_POST['edit_id'] ?? 0);

    if (!$title) { $error = 'Title is required.'; }
    else {
        if (!$slug) $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/','-',$title));
        $data = ['title'=>$title,'slug'=>$slug,'icon'=>$icon,'color'=>$color,'description'=>$description,'features'=>$features,'status'=>$status,'sort_order'=>$sort];
        if ($editId) {
            $set = implode(',', array_map(fn($k)=>"`$k`=?", array_keys($data)));
            query("UPDATE services SET $set WHERE id=?", [...array_values($data), $editId]);
        } else {
            $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
            $ph   = implode(',', array_fill(0,count($data),'?'));
            query("INSERT INTO services ($cols) VALUES ($ph)", array_values($data));
        }
        $success = 'Service saved!';
        $editing = null;
    }
}

$services = fetchAll("SELECT * FROM services ORDER BY sort_order ASC");
$e = $editing ?? [];
require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title">Services <span class="badge badge-gray" style="margin-left:8px;"><?= count($services) ?></span></span></div>
    <table class="tm-table">
      <thead><tr><th>Service</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($services as $svc): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:rgba(34,197,94,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="<?= htmlspecialchars($svc['icon']) ?>" style="color:<?= htmlspecialchars($svc['color']) ?>;font-size:0.85rem;"></i>
            </div>
            <div>
              <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($svc['title']) ?></div>
              <div style="font-size:0.72rem;color:#64748b;"><?= htmlspecialchars(substr($svc['description']??'',0,60)) ?>...</div>
            </div>
          </div>
        </td>
        <td><a href="?toggle=<?= $svc['id'] ?>" style="text-decoration:none;"><span class="badge <?= $svc['status']==='active'?'badge-green':'badge-amber' ?>"><?= ucfirst($svc['status']) ?></span></a></td>
        <td style="color:#64748b;"><?= $svc['sort_order'] ?></td>
        <td>
          <div class="gap-8">
            <a href="?edit=<?= $svc['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
            <a href="?delete=<?= $svc['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="tm-card" style="position:sticky;top:80px;">
    <div class="tm-card-header"><span class="tm-card-title"><?= $editing ? 'Edit' : 'Add' ?> Service</span></div>
    <form method="POST">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group"><label>Title *</label><input type="text" name="title" id="title-input" value="<?= htmlspecialchars($e['title']??'') ?>" required></div>
      <div class="form-group"><label>Slug</label><input type="text" name="slug" id="slug-input" value="<?= htmlspecialchars($e['slug']??'') ?>"></div>
      <div class="form-row">
        <div class="form-group"><label>FA Icon Class</label><input type="text" name="icon" value="<?= htmlspecialchars($e['icon']??'fa-solid fa-star') ?>" placeholder="fa-solid fa-globe"></div>
        <div class="form-group"><label>Color</label><input type="color" name="color" value="<?= htmlspecialchars($e['color']??'#22c55e') ?>" style="height:42px;padding:4px;cursor:pointer;"></div>
      </div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= htmlspecialchars($e['description']??'') ?></textarea></div>
      <div class="form-group"><label>Features (comma separated)</label><input type="text" name="features" value="<?= htmlspecialchars($e['features']??'') ?>" placeholder="Feature 1, Feature 2, Feature 3"></div>
      <div class="form-row">
        <div class="form-group"><label>Status</label>
          <select name="status">
            <option value="active" <?= ($e['status']??'active')==='active'?'selected':'' ?>>Active</option>
            <option value="draft" <?= ($e['status']??'')==='draft'?'selected':'' ?>>Draft</option>
          </select>
        </div>
        <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $e['sort_order']??0 ?>" min="0"></div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Save Service</button>
      <?php if ($editing): ?><a href="<?= SITE_URL ?>/admin/services.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:8px;">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>

<script>
const t=document.getElementById('title-input'),s=document.getElementById('slug-input');
t.addEventListener('input',()=>{ if(!s.dataset.manual) s.value=t.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''); });
s.addEventListener('input',()=>s.dataset.manual='1');
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
