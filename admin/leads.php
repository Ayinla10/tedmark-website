<?php
$pageTitle = 'Leads';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM leads WHERE id=?", [(int)$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/leads.php'); exit;
}

// Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_filter(array_map('intval', $_POST['ids']));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($_POST['bulk_action'] === 'delete') {
            query("DELETE FROM leads WHERE id IN ($placeholders)", $ids);
        } elseif (in_array($_POST['bulk_action'] ?? '', ['contacted','converted'], true)) {
            query("UPDATE leads SET status=? WHERE id IN ($placeholders)", [$_POST['bulk_action'], ...$ids]);
        }
    }
    header('Location: ' . SITE_URL . '/admin/leads.php?page=' . (int)($_POST['current_page'] ?? 1)); exit;
}

$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

try {
    $totalCount = (int)(fetchOne("SELECT COUNT(*) AS c FROM leads")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($totalCount / $perPage));
    $leads = fetchAll("SELECT * FROM leads ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
} catch(Exception $e) { $leads = []; $totalCount = 0; $totalPages = 1; }

require_once __DIR__ . '/includes/admin-layout.php';
?>

<form method="POST" id="bulk-form">
<input type="hidden" name="current_page" value="<?= $page ?>">
<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">Newsletter Leads <span class="badge badge-gray" style="margin-left:8px;"><?= $totalCount ?></span></span>
    <div class="gap-8">
      <button type="button" onclick="exportCSV()" class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Export CSV</button>
      <button type="submit" name="bulk_action" value="converted" class="btn btn-ghost btn-sm" onclick="return confirm('Mark selected as converted?')"><i class="fa-solid fa-check"></i> Mark Converted</button>
      <button type="submit" name="bulk_action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected leads?')"><i class="fa-solid fa-trash"></i> Delete Selected</button>
    </div>
  </div>
  <table class="tm-table" id="leads-table">
    <thead><tr>
      <th style="width:32px;"><input type="checkbox" id="select-all"></th>
      <th>Name</th><th>Email</th><th>Source</th><th>Status</th><th>Date</th><th>Actions</th>
    </tr></thead>
    <tbody>
      <?php if (empty($leads)): ?>
      <tr><td colspan="7" style="text-align:center;color:#64748b;padding:40px 0;">No leads yet.</td></tr>
      <?php else: foreach ($leads as $l): ?>
      <tr>
        <td><input type="checkbox" name="ids[]" value="<?= $l['id'] ?>" class="row-check"></td>
        <td style="font-weight:600;color:#fff;"><?= htmlspecialchars($l['name'] ?: '—') ?></td>
        <td><a href="mailto:<?= htmlspecialchars($l['email']) ?>" style="color:#22c55e;text-decoration:none;"><?= htmlspecialchars($l['email']) ?></a></td>
        <td style="color:#64748b;font-size:0.8rem;"><?= htmlspecialchars($l['source'] ?? 'website') ?></td>
        <td><span class="badge <?= $l['status']==='converted'?'badge-green':($l['status']==='contacted'?'badge-blue':'badge-gray') ?>"><?= ucfirst($l['status']) ?></span></td>
        <td style="color:#64748b;font-size:0.78rem;white-space:nowrap;"><?= date('M j, Y', strtotime($l['created_at'])) ?></td>
        <td><a href="?delete=<?= $l['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
</form>

<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:20px;">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
  <a href="?page=<?= $p ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<script>
document.getElementById('select-all')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
function exportCSV() {
    const rows = [['Name','Email','Source','Status','Date']];
    document.querySelectorAll('#leads-table tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length > 1) rows.push([...cells].slice(1,6).map(c => '"'+c.textContent.trim()+'"'));
    });
    const csv = rows.map(r => r.join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'tedmark-leads.csv';
    a.click();
}
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
