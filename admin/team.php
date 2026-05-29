<?php
$pageTitle = 'Team Members';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$editing = null;
$success = $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM team_members WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/team.php'); exit;
}
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM team_members WHERE id = ?", [$_GET['edit']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $role    = trim($_POST['role'] ?? '');
    $bio     = trim($_POST['bio'] ?? '');
    $avatar  = trim($_POST['avatar'] ?? '');
    $linkedin= trim($_POST['linkedin'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');
    $status  = $_POST['status'] ?? 'active';
    $sort    = (int)($_POST['sort_order'] ?? 0);
    $editId  = (int)($_POST['edit_id'] ?? 0);

    if (!$name) { $error = 'Name is required.'; }
    else {
        $data = ['name'=>$name,'role'=>$role,'bio'=>$bio,'avatar'=>$avatar,'linkedin'=>$linkedin,'twitter'=>$twitter,'status'=>$status,'sort_order'=>$sort];
        if ($editId) {
            $set = implode(',', array_map(fn($k)=>"`$k`=?", array_keys($data)));
            query("UPDATE team_members SET $set WHERE id=?", [...array_values($data), $editId]);
        } else {
            $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
            $ph   = implode(',', array_fill(0,count($data),'?'));
            query("INSERT INTO team_members ($cols) VALUES ($ph)", array_values($data));
        }
        $success = 'Team member saved!';
        $editing = null;
    }
}

$team = fetchAll("SELECT * FROM team_members ORDER BY sort_order ASC, created_at DESC");
$e = $editing ?? [];

require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

  <div class="tm-card">
    <div class="tm-card-header"><span class="tm-card-title">Team Members <span class="badge badge-gray" style="margin-left:8px;"><?= count($team) ?></span></span></div>
    <?php if (empty($team)): ?>
    <p style="color:#64748b;text-align:center;padding:32px 0;">No team members yet. Add one →</p>
    <?php else: ?>
    <table class="tm-table">
      <thead><tr><th>Member</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($team as $m): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <?php if ($m['avatar']): ?>
            <img src="<?= htmlspecialchars($m['avatar']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
            <div style="width:36px;height:36px;border-radius:50%;background:#1e293b;display:flex;align-items:center;justify-content:center;color:#22c55e;font-weight:700;"><?= strtoupper(substr($m['name'],0,1)) ?></div>
            <?php endif; ?>
            <span style="font-weight:600;color:#fff;"><?= htmlspecialchars($m['name']) ?></span>
          </div>
        </td>
        <td style="color:#94a3b8;"><?= htmlspecialchars($m['role']??'—') ?></td>
        <td><span class="badge <?= $m['status']==='active'?'badge-green':'badge-gray' ?>"><?= ucfirst($m['status']) ?></span></td>
        <td>
          <div class="gap-8">
            <a href="?edit=<?= $m['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
            <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="tm-card" style="position:sticky;top:80px;">
    <div class="tm-card-header"><span class="tm-card-title"><?= $editing ? 'Edit' : 'Add' ?> Member</span></div>
    <form method="POST">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group"><label>Full Name *</label><input type="text" name="name" value="<?= htmlspecialchars($e['name']??'') ?>" required></div>
      <div class="form-group"><label>Role / Title</label><input type="text" name="role" value="<?= htmlspecialchars($e['role']??'') ?>" placeholder="CEO, Developer..."></div>
      <div class="form-group"><label>Bio</label><textarea name="bio" rows="3"><?= htmlspecialchars($e['bio']??'') ?></textarea></div>
      <div class="form-group"><label>Photo URL</label><input type="text" name="avatar" value="<?= htmlspecialchars($e['avatar']??'') ?>" placeholder="https://..."></div>
      <div class="form-group"><label>LinkedIn URL</label><input type="text" name="linkedin" value="<?= htmlspecialchars($e['linkedin']??'') ?>"></div>
      <div class="form-group"><label>Twitter URL</label><input type="text" name="twitter" value="<?= htmlspecialchars($e['twitter']??'') ?>"></div>
      <div class="form-row">
        <div class="form-group"><label>Status</label>
          <select name="status">
            <option value="active" <?= ($e['status']??'active')==='active'?'selected':'' ?>>Active</option>
            <option value="hidden" <?= ($e['status']??'')==='hidden'?'selected':'' ?>>Hidden</option>
          </select>
        </div>
        <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $e['sort_order']??0 ?>"></div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      <?php if ($editing): ?><a href="<?= SITE_URL ?>/admin/team.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:8px;">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
