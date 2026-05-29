<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$adminTitle = 'Leads';

if (isset($_GET['delete'])) { query("DELETE FROM leads WHERE id=?",[(int)$_GET['delete']]); flash('success','Deleted.'); redirect(SITE_URL.'/admin/leads.php'); }

$leads = fetchAll("SELECT * FROM leads ORDER BY created_at DESC");
require_once __DIR__ . '/includes/admin-layout.php';
?>

<div class="space-y-5">
    <div class="flex items-center justify-between">
        <p class="text-slate-400"><?= count($leads) ?> total leads</p>
        <a href="#" onclick="exportCSV()" class="admin-btn admin-btn-outline text-slate-300">Export CSV</a>
    </div>
    <div class="admin-card overflow-auto">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Source</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($leads)): ?><tr><td colspan="7" class="text-center text-slate-500 py-10">No leads yet</td></tr>
                <?php else: foreach ($leads as $l): ?>
                <tr>
                    <td class="text-white font-medium"><?= sanitize($l['name'] ?? '—') ?></td>
                    <td><a href="mailto:<?= sanitize($l['email']) ?>" class="text-brand-400 hover:text-brand-300 text-sm"><?= sanitize($l['email']) ?></a></td>
                    <td class="text-slate-300"><?= sanitize($l['company'] ?? '—') ?></td>
                    <td><span class="text-slate-400 text-xs"><?= sanitize($l['source'] ?? 'website') ?></span></td>
                    <td><span class="badge-status status-<?= $l['status']==='new'?'unread':($l['status']==='converted'?'confirmed':'draft') ?>"><?= ucfirst($l['status']) ?></span></td>
                    <td class="text-xs text-slate-400"><?= formatDate($l['created_at']) ?></td>
                    <td><a href="?delete=<?=$l['id']?>" onclick="return confirm('Delete?')" class="admin-btn admin-btn-danger admin-btn-sm">Del</a></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function exportCSV() {
    const rows = [['Name','Email','Company','Source','Status','Date']];
    document.querySelectorAll('.admin-table tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length > 1) rows.push([...cells].slice(0,6).map(c => '"'+c.textContent.trim()+'"'));
    });
    const csv = rows.map(r => r.join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'tedmark-leads.csv';
    a.click();
}
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
