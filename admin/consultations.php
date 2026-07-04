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

// Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_filter(array_map('intval', $_POST['ids']));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($_POST['bulk_action'] === 'delete') {
            query("DELETE FROM consultations WHERE id IN ($placeholders)", $ids);
        } elseif (in_array($_POST['bulk_action'] ?? '', ['confirmed','completed','cancelled'], true)) {
            query("UPDATE consultations SET status=? WHERE id IN ($placeholders)", [$_POST['bulk_action'], ...$ids]);
        }
    }
    header('Location: ' . SITE_URL . '/admin/consultations.php?filter=' . urlencode($_POST['current_filter'] ?? 'all') . '&page=' . (int)($_POST['current_page'] ?? 1)); exit;
}

$filterRaw = $_GET['filter'] ?? 'all';
$filter    = in_array($filterRaw, ['all','pending','confirmed','completed','cancelled'], true) ? $filterRaw : 'all';
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

try {
    $where = $filter !== 'all' ? " WHERE status = " . db()->quote($filter) : '';
    $totalCount = (int)(fetchOne("SELECT COUNT(*) AS c FROM consultations" . $where)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($totalCount / $perPage));
    $consultations = fetchAll("SELECT * FROM consultations $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
} catch(Exception $e) { $consultations = []; $totalCount = 0; $totalPages = 1; }

require_once __DIR__ . '/includes/admin-layout.php';
?>

<form method="POST" id="bulk-form">
<input type="hidden" name="current_page" value="<?= $page ?>">
<input type="hidden" name="current_filter" value="<?= htmlspecialchars($filter) ?>">

<div class="tm-card" style="margin-bottom:20px;padding:16px 20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php foreach (['all','pending','confirmed','completed','cancelled'] as $f): ?>
      <a href="?filter=<?= $f ?>" class="btn btn-sm <?= $filter === $f ? 'btn-primary' : 'btn-ghost' ?>"><?= ucfirst($f) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="gap-8">
      <span style="color:#64748b;font-size:0.85rem;"><?= $totalCount ?> bookings</span>
      <button type="submit" name="bulk_action" value="confirmed" class="btn btn-ghost btn-sm" onclick="return confirm('Confirm selected bookings?')"><i class="fa-solid fa-check"></i> Confirm</button>
      <button type="submit" name="bulk_action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected bookings?')"><i class="fa-solid fa-trash"></i> Delete Selected</button>
    </div>
  </div>
</div>

<div class="tm-card">
  <table class="tm-table">
    <thead>
      <tr>
        <th style="width:32px;"><input type="checkbox" id="select-all"></th>
        <th>Name / Contact</th><th>Business</th><th>Package Interest</th><th>Challenge</th><th>Status</th><th>Date</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($consultations)): ?>
      <tr><td colspan="8" style="text-align:center;color:#64748b;padding:40px 0;">No consultation bookings yet.</td></tr>
      <?php else: foreach ($consultations as $c): ?>
      <tr>
        <td><input type="checkbox" name="ids[]" value="<?= $c['id'] ?>" class="row-check"></td>
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
</form>

<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:20px;">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
  <a href="?filter=<?= $filter ?>&page=<?= $p ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<script>
document.getElementById('select-all')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
