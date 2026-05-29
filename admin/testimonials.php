<?php
$pageTitle = 'Testimonials';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin-layout.php';

$editing = null;
$success = $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM testimonials WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/testimonials.php'); exit;
}
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT * FROM testimonials WHERE id = ?", [$_GET['edit']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $role    = trim($_POST['role'] ?? '');
    $quote   = trim($_POST['quote'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 5);
    $status  = $_POST['status'] ?? 'active';
    $editId  = (int)($_POST['edit_id'] ?? 0);

    if (!$name || !$quote) { $error = 'Name and quote are required.'; }
    else {
        $data = ['name'=>$name,'company'=>$company,'role'=>$role,'quote'=>$quote,'rating'=>$rating,'status'=>$status];
        if ($editId) {
            $set = implode(',', array_map(fn($k) => "`$k`=?", array_keys($data)));
            query("UPDATE testimonials SET $set WHERE id=?", [...array_values($data), $editId]);
        } else {
            $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($data)));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            query("INSERT INTO testimonials ($cols) VALUES ($ph)", array_values($data));
        }
        $success = 'Testimonial saved!';
        $editing = null;
    }
}

$testimonials = fetchAll("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC");
$e = $editing ?? [];
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

  <!-- List -->
  <div class="tm-card">
    <div class="tm-card-header">
      <span class="tm-card-title">Testimonials <span class="badge badge-gray" style="margin-left:8px;"><?= count($testimonials) ?></span></span>
    </div>
    <?php if (empty($testimonials)): ?>
    <p style="color:#64748b;text-align:center;padding:32px 0;">No testimonials yet. Add one →</p>
    <?php else: ?>
    <?php foreach ($testimonials as $t): ?>
    <div style="padding:16px;border:1px solid #1e293b;border-radius:10px;margin-bottom:12px;">
      <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
        <div>
          <div style="font-weight:700;color:#fff;"><?= htmlspecialchars($t['name']) ?></div>
          <div style="font-size:0.78rem;color:#64748b;"><?= htmlspecialchars($t['role']??'') ?> <?= $t['company'] ? '@ '.htmlspecialchars($t['company']) : '' ?></div>
        </div>
        <div class="gap-8">
          <span class="badge <?= $t['status']==='active'?'badge-green':'badge-gray' ?>"><?= ucfirst($t['status']) ?></span>
          <a href="?edit=<?= $t['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
          <a href="?delete=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
        </div>
      </div>
      <p style="color:#94a3b8;font-size:0.85rem;font-style:italic;line-height:1.6;">"<?= htmlspecialchars(substr($t['quote'],0,150)) ?>..."</p>
      <div style="margin-top:6px;color:#f59e0b;font-size:0.8rem;"><?= str_repeat('★', $t['rating']) ?><?= str_repeat('☆', 5-$t['rating']) ?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Form -->
  <div class="tm-card" style="position:sticky;top:80px;">
    <div class="tm-card-header"><span class="tm-card-title"><?= $editing ? 'Edit' : 'Add' ?> Testimonial</span></div>
    <form method="POST">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group"><label>Client Name *</label><input type="text" name="name" value="<?= htmlspecialchars($e['name']??'') ?>" required></div>
      <div class="form-group"><label>Company</label><input type="text" name="company" value="<?= htmlspecialchars($e['company']??'') ?>"></div>
      <div class="form-group"><label>Role / Title</label><input type="text" name="role" value="<?= htmlspecialchars($e['role']??'') ?>" placeholder="CEO, Manager..."></div>
      <div class="form-group"><label>Quote *</label><textarea name="quote" rows="4" required><?= htmlspecialchars($e['quote']??'') ?></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>Rating</label>
          <select name="rating">
            <?php for($i=5;$i>=1;$i--): ?>
            <option value="<?= $i ?>" <?= ($e['rating']??5)==$i?'selected':'' ?>><?= $i ?> Stars</option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group"><label>Status</label>
          <select name="status">
            <option value="active" <?= ($e['status']??'active')==='active'?'selected':'' ?>>Active</option>
            <option value="hidden" <?= ($e['status']??'')==='hidden'?'selected':'' ?>>Hidden</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      <?php if ($editing): ?><a href="<?= SITE_URL ?>/admin/testimonials.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:8px;">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
