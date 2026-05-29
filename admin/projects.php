<?php
$pageTitle = 'Portfolio Projects';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin-layout.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM projects WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/projects.php?msg=deleted'); exit;
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $proj = fetchOne("SELECT status FROM projects WHERE id = ?", [$_GET['toggle']]);
    if ($proj) query("UPDATE projects SET status = ? WHERE id = ?", [$proj['status']==='active'?'draft':'active', $_GET['toggle']]);
    header('Location: ' . SITE_URL . '/admin/projects.php'); exit;
}

$projects = fetchAll("SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC");
$msg = $_GET['msg'] ?? '';
?>

<?php if ($msg==='saved'): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> Project saved!</div><?php endif; ?>
<?php if ($msg==='deleted'): ?><div class="alert alert-error"><i class="fa-solid fa-trash"></i> Project deleted.</div><?php endif; ?>

<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">All Projects <span class="badge badge-gray" style="margin-left:8px;"><?= count($projects) ?></span></span>
    <a href="<?= SITE_URL ?>/admin/project-edit.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Project</a>
  </div>
  <?php if (empty($projects)): ?>
  <div style="text-align:center;padding:48px 0;color:#64748b;">
    <i class="fa-solid fa-folder-open" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.4;"></i>
    No projects yet. <a href="<?= SITE_URL ?>/admin/project-edit.php" style="color:#22c55e;">Add your first project</a>
  </div>
  <?php else: ?>
  <table class="tm-table">
    <thead><tr><th>Project</th><th>Client</th><th>Category</th><th>Result</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($projects as $p): ?>
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;border-radius:8px;background:<?= htmlspecialchars($p['bg']??'#1e293b') ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="<?= htmlspecialchars($p['icon']??'fa-solid fa-briefcase') ?>" style="color:<?= htmlspecialchars($p['color']??'#22c55e') ?>;font-size:0.9rem;"></i>
          </div>
          <div>
            <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($p['title']) ?></div>
            <div style="font-size:0.72rem;color:#64748b;"><?= htmlspecialchars($p['year']??'') ?></div>
          </div>
        </div>
      </td>
      <td style="color:#94a3b8;"><?= htmlspecialchars($p['client']??'—') ?></td>
      <td><span class="badge badge-blue"><?= htmlspecialchars($p['category']??'—') ?></span></td>
      <td style="color:#22c55e;font-size:0.8rem;font-weight:600;"><?= htmlspecialchars($p['result']??'—') ?></td>
      <td><a href="?toggle=<?= $p['id'] ?>" style="text-decoration:none;"><span class="badge <?= $p['status']==='active'?'badge-green':'badge-amber' ?>"><?= ucfirst($p['status']) ?></span></a></td>
      <td>
        <div class="gap-8">
          <a href="<?= SITE_URL ?>/admin/project-edit.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
          <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this project?')"><i class="fa-solid fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
