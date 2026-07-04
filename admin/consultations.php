<?php
$pageTitle = 'Consultations';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['status']) && isset($_GET['id'])) {
    $newStatus = in_array($_GET['status'], ['pending','confirmed','completed','cancelled']) ? $_GET['status'] : 'pending';
    query("UPDATE consultations SET status=? WHERE id=?", [$newStatus, (int)$_GET['id']]);
    header('Location: ' . SITE_URL . '/admin/consultations.php'); exit;
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM consultations WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/consultations.php'); exit;
}

$filter = $_GET['filter'] ?? 'all';
try {
    $sql = "SELECT * FROM consultations";
    if ($filter !== 'all') $sql .= " WHERE status = " . db()->quote($filter);
    $sql .= " ORDER BY created_at DESC";
    $consultations = fetchAll($sql);
} catch(Exception $e) { $consultations = []; }

require_once __DIR__ . '/includes/admin-layout.php';
?>

<div class="tm-card" style="margin-bottom:20px;padding:16px 20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php foreach (['all','pending','confirmed','completed','cancelled'] as $f): ?>
      <a href="?filter=<?= $f ?>" class="btn btn-sm <?= $filter === $f ? 'btn-primary' : 'btn-ghost' ?>"><?= ucfirst($f) ?></a>
      <?php endforeach; ?>
    </div>
    <span style="color:#64748b;font-size:0.85rem;"><?= count($consultations) ?> bookings</span>
  </div>
</div>

<div class="tm-card">
  <table class="tm-table">
    <thead>
      <tr><th>Name / Contact</th><th>Business</th><th>Package Interest</th><th>Challenge</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (empty($consultations)): ?>
      <tr><td colspan="7" style="text-align:center;color:#64748b;padding:40px 0;">No consultation bookings yet.</td></tr>
      <?php else: foreach ($consultations as $c): ?>
      <tr>
        <td>
          <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($c['name']) ?></div>
          <div style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($c['email']) ?></div>
          <?php if ($c['phone']): ?><div style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($c['phone']) ?></div><?php endif; ?>
        </td>
        <td>
          <div style="color:#e2e8f0;"><?= htmlspecialchars($c['business_name'] ?: '—') ?></div>
          <div style="font-size:0.72rem;color:#64748b;"><?= htmlspecialchars($c['industry'] ?? '') ?></div>
        </td>
        <td style="color:#94a3b8;font-size:0.82rem;max-width:160px;"><?= htmlspecialchars($c['package_interest'] ?: '—') ?></td>
        <td style="color:#94a3b8;font-size:0.8rem;max-width:220px;"><?= htmlspecialchars(substr($c['main_challenge'] ?? '', 0, 100)) ?></td>
        <td><span class="badge <?= [
            'pending'=>'badge-amber','confirmed'=>'badge-blue','completed'=>'badge-green','cancelled'=>'badge-red'
        ][$c['status']] ?? 'badge-gray' ?>"><?= ucfirst($c['status']) ?></span></td>
        <td style="color:#64748b;font-size:0.78rem;white-space:nowrap;"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
        <td>
          <div class="gap-8">
            <a href="?id=<?= $c['id'] ?>&status=confirmed" class="btn btn-ghost btn-sm" title="Confirm"><i class="fa-solid fa-check"></i></a>
            <a href="?id=<?= $c['id'] ?>&status=completed" class="btn btn-ghost btn-sm" title="Mark done"><i class="fa-solid fa-flag-checkered"></i></a>
            <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this booking?')"><i class="fa-solid fa-trash"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
