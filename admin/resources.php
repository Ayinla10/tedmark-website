<?php
$pageTitle = 'Resources';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid = (int)($_POST['r_id'] ?? 0);
    $data = ['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),'category'=>trim($_POST['category']??''),'status'=>$_POST['status']??'active','featured'=>(int)isset($_POST['featured'])];
    if ($data['title']) {
        if (!empty($_FILES['resource_file']['name'])) {
            $f = uploadFile($_FILES['resource_file'],'resources',['pdf','xlsx','docx','pptx','zip']);
            if ($f) { $data['file_path']=$f; $data['file_type']=pathinfo($f,PATHINFO_EXTENSION); $data['file_size']=round($_FILES['resource_file']['size']/1024).'KB'; }
        }
        if ($rid) { update('resources',$data,'id=?',[$rid]); }
        else { insert('resources',$data); }
        header('Location: ' . SITE_URL . '/admin/resources.php'); exit;
    }
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM resources WHERE id=?", [(int)$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/resources.php'); exit;
}

try {
    $resources = fetchAll("SELECT * FROM resources ORDER BY featured DESC, created_at DESC");
    $edit = $id ? fetchOne("SELECT * FROM resources WHERE id=?", [$id]) : null;
} catch(Exception $e) { $resources = []; $edit = null; }

require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($action !== 'list'): ?>
<div class="tm-card" style="max-width:600px;">
  <div class="tm-card-header">
    <span class="tm-card-title"><?= $edit ? 'Edit' : 'Upload New' ?> Resource</span>
    <a href="<?= SITE_URL ?>/admin/resources.php" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="r_id" value="<?= $edit['id'] ?? 0 ?>">
    <div class="form-group"><label>Resource Title *</label><input type="text" name="title" required value="<?= htmlspecialchars($edit['title']??'') ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= htmlspecialchars($edit['description']??'') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Category</label><input type="text" name="category" placeholder="e.g. Templates, Guides" value="<?= htmlspecialchars($edit['category']??'') ?>"></div>
      <div class="form-group"><label>Status</label>
        <select name="status">
          <option value="active" <?= ($edit['status']??'active')==='active'?'selected':'' ?>>Active</option>
          <option value="draft" <?= ($edit['status']??'')==='draft'?'selected':'' ?>>Draft</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>File Upload (PDF, Excel, Word, etc.)</label>
      <?php if (!empty($edit['file_path'])): ?><div style="color:#64748b;font-size:0.8rem;margin-bottom:8px;">Current: <?= htmlspecialchars($edit['file_path']) ?></div><?php endif; ?>
      <input type="file" name="resource_file" accept=".pdf,.xlsx,.docx,.pptx,.zip">
    </div>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:16px;">
      <input type="checkbox" name="featured" <?= !empty($edit['featured'])?'checked':'' ?>>
      <span style="color:#94a3b8;font-size:0.85rem;">Feature this resource</span>
    </label>
    <button type="submit" class="btn btn-primary"><?= $edit ? 'Update' : 'Upload' ?></button>
    <a href="<?= SITE_URL ?>/admin/resources.php" class="btn btn-ghost">Cancel</a>
  </form>
</div>
<?php else: ?>
<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">Resources <span class="badge badge-gray" style="margin-left:8px;"><?= count($resources) ?></span></span>
    <a href="?action=new" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Upload Resource</a>
  </div>
  <table class="tm-table">
    <thead><tr><th>Title</th><th>Category</th><th>Type</th><th>Downloads</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (empty($resources)): ?>
      <tr><td colspan="6" style="text-align:center;color:#64748b;padding:40px 0;">No resources yet. <a href="?action=new" style="color:#22c55e;">Upload one →</a></td></tr>
      <?php else: foreach ($resources as $r): ?>
      <tr>
        <td>
          <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($r['title']) ?></div>
          <?php if ($r['featured']): ?><span style="color:#f59e0b;font-size:0.72rem;">⭐ Featured</span><?php endif; ?>
        </td>
        <td style="color:#94a3b8;"><?= htmlspecialchars($r['category'] ?: '—') ?></td>
        <td><span class="badge badge-gray" style="font-family:monospace;"><?= strtoupper($r['file_type'] ?? 'PDF') ?></span></td>
        <td style="color:#94a3b8;"><?= number_format($r['download_count'] ?? 0) ?></td>
        <td><span class="badge <?= $r['status']==='active'?'badge-green':'badge-amber' ?>"><?= ucfirst($r['status']) ?></span></td>
        <td>
          <div class="gap-8">
            <a href="?action=edit&id=<?= $r['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
            <a href="?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
