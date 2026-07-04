<?php
$pageTitle = 'Leads';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM leads WHERE id=?", [(int)$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/leads.php'); exit;
}

try { $leads = fetchAll("SELECT * FROM leads ORDER BY created_at DESC"); }
catch(Exception $e) { $leads = []; }

require_once __DIR__ . '/includes/admin-layout.php';
?>

<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">Newsletter Leads <span class="badge badge-gray" style="margin-left:8px;"><?= count($leads) ?></span></span>
    <button onclick="exportCSV()" class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Export CSV</button>
  </div>
  <table class="tm-table" id="leads-table">
    <thead><tr><th>Name</th><th>Email</th><th>Source</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (empty($leads)): ?>
      <tr><td colspan="6" style="text-align:center;color:#64748b;padding:40px 0;">No leads yet.</td></tr>
      <?php else: foreach ($leads as $l): ?>
      <tr>
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

<script>
function exportCSV() {
    const rows = [['Name','Email','Source','Status','Date']];
    document.querySelectorAll('#leads-table tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length > 1) rows.push([...cells].slice(0,5).map(c => '"'+c.textContent.trim()+'"'));
    });
    const csv = rows.map(r => r.join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'tedmark-leads.csv';
    a.click();
}
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
