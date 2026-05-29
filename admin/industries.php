<?php
$pageTitle = 'Industries';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$editing = null;
$success = $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM industries WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/industries.php'); exit;
}
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM industries WHERE id = ?", [$_GET['edit']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $icon    = trim($_POST['icon'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $status  = $_POST['status'] ?? 'active';
    $sort    = (int)($_POST['sort_order'] ?? 0);
    $editId  = (int)($_POST['edit_id'] ?? 0);

    if (!$name) { $error = 'Industry name is required.'; }
    else {
        $data = ['title'=>$name,'icon'=>$icon,'description'=>$desc,'status'=>$status,'sort_order'=>$sort];
        if ($editId) {
            $set = implode(',', array_map(fn($k)=>"`$k`=?", array_keys($data)));
            query("UPDATE industries SET $set WHERE id=?", [...array_values($data), $editId]);
        } else {
            $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
            $ph   = implode(',', array_fill(0,count($data),'?'));
            query("INSERT INTO industries ($cols) VALUES ($ph)", array_values($data));
        }
        $success = 'Industry saved!';
        $editing = null;
    }
}

$industries = fetchAll("SELECT * FROM industries ORDER BY sort_order ASC, title ASC");
$e = $editing ?? [];

require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

  <div class="tm-card">
    <div class="tm-card-header">
      <span class="tm-card-title">Industries <span class="badge badge-gray" style="margin-left:8px;"><?= count($industries) ?></span></span>
    </div>
    <?php if (empty($industries)): ?>
    <p style="color:#64748b;text-align:center;padding:32px 0;">No industries yet. Add one →</p>
    <?php else: ?>
    <table class="tm-table">
      <thead><tr><th>Industry</th><th>Icon</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($industries as $ind): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:8px;background:#1e293b;display:flex;align-items:center;justify-content:center;color:#22c55e;">
              <i class="fa-solid <?= htmlspecialchars($ind['icon'] ?: 'fa-building') ?>"></i>
            </div>
            <div>
              <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($ind['title']) ?></div>
              <?php if ($ind['description']): ?><div style="font-size:0.78rem;color:#64748b;"><?= htmlspecialchars(substr($ind['description'],0,60)) ?>...</div><?php endif; ?>
            </div>
          </div>
        </td>
        <td style="color:#94a3b8;font-size:0.8rem;"><?= htmlspecialchars($ind['icon']??'—') ?></td>
        <td><span class="badge <?= $ind['status']==='active'?'badge-green':'badge-gray' ?>"><?= ucfirst($ind['status']) ?></span></td>
        <td style="color:#94a3b8;"><?= $ind['sort_order'] ?></td>
        <td>
          <div class="gap-8">
            <a href="?edit=<?= $ind['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
            <a href="?delete=<?= $ind['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this industry?')"><i class="fa-solid fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="tm-card" style="position:sticky;top:80px;">
    <div class="tm-card-header"><span class="tm-card-title"><?= $editing ? 'Edit' : 'Add' ?> Industry</span></div>
    <form method="POST">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group">
        <label>Industry Name *</label>
        <input type="text" name="name" value="<?= htmlspecialchars($e['title']??'') ?>" required placeholder="e.g. Healthcare, Finance...">
      </div>
      <div class="form-group">
        <label>Font Awesome Icon Class</label>
        <input type="text" name="icon" value="<?= htmlspecialchars($e['icon']??'') ?>" placeholder="fa-hospital, fa-coins...">
        <small style="color:#64748b;font-size:0.75rem;">Use FA icon name e.g. <code style="background:#0b1528;padding:1px 4px;border-radius:4px;">fa-hospital</code></small>
      </div>
      <div class="form-group">
        <label>Short Description</label>
        <textarea name="description" rows="3" placeholder="Brief description of this industry..."><?= htmlspecialchars($e['description']??'') ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="active" <?= ($e['status']??'active')==='active'?'selected':'' ?>>Active</option>
            <option value="hidden" <?= ($e['status']??'')==='hidden'?'selected':'' ?>>Hidden</option>
          </select>
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" name="sort_order" value="<?= $e['sort_order']??0 ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      <?php if ($editing): ?><a href="<?= SITE_URL ?>/admin/industries.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:8px;">Cancel</a><?php endif; ?>
    </form>

    <!-- Icon reference -->
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid #1e293b;">
      <p style="color:#64748b;font-size:0.75rem;margin-bottom:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Common Icons</p>
      <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php $icons=['fa-hospital'=>'Health','fa-coins'=>'Finance','fa-cart-shopping'=>'Retail','fa-graduation-cap'=>'Education','fa-truck'=>'Logistics','fa-building-columns'=>'Government','fa-leaf'=>'Agriculture','fa-gavel'=>'Legal','fa-hotel'=>'Hospitality','fa-microchip'=>'Tech','fa-hard-hat'=>'Construction','fa-oil-well'=>'Energy']; ?>
        <?php foreach($icons as $ic=>$lbl): ?>
        <button type="button" onclick="document.querySelector('[name=icon]').value='<?= $ic ?>'" style="background:#0b1528;border:1px solid #1e293b;color:#94a3b8;padding:4px 8px;border-radius:6px;font-size:0.75rem;cursor:pointer;display:flex;align-items:center;gap:4px;">
          <i class="fa-solid <?= $ic ?>"></i> <?= $lbl ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
