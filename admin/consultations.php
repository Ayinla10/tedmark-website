<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$adminTitle = 'Consultations';

if (isset($_GET['status']) && isset($_GET['id'])) {
    $newStatus = in_array($_GET['status'], ['pending','confirmed','completed','cancelled']) ? $_GET['status'] : 'pending';
    update('consultations', ['status' => $newStatus], 'id = ?', [(int)$_GET['id']]);
    flash('success', 'Status updated.');
    redirect(SITE_URL . '/admin/consultations.php');
}

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM consultations";
if ($filter !== 'all') $sql .= " WHERE status = " . db()->quote($filter);
$sql .= " ORDER BY created_at DESC";
$consultations = fetchAll($sql);

require_once __DIR__ . '/includes/admin-layout.php';
?>

<div class="space-y-5">
    <div class="flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2">
            <?php foreach (['all','pending','confirmed','completed','cancelled'] as $f): ?>
            <a href="?filter=<?= $f ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors <?= $filter === $f ? 'bg-brand-500 text-white' : 'bg-white/5 text-slate-400 hover:bg-white/10' ?>"><?= ucfirst($f) ?></a>
            <?php endforeach; ?>
        </div>
        <p class="text-slate-400 text-sm"><?= count($consultations) ?> bookings</p>
    </div>

    <div class="admin-card overflow-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name / Contact</th>
                    <th>Company</th>
                    <th>Interest</th>
                    <th>Preferred Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($consultations)): ?>
                <tr><td colspan="7" class="text-center text-slate-500 py-10">No consultations yet</td></tr>
                <?php else: ?>
                <?php foreach ($consultations as $c): ?>
                <tr>
                    <td>
                        <div class="font-semibold text-white"><?= sanitize($c['name']) ?></div>
                        <div class="text-slate-400 text-xs"><?= sanitize($c['email']) ?></div>
                        <div class="text-slate-500 text-xs"><?= sanitize($c['phone'] ?? '') ?></div>
                    </td>
                    <td>
                        <div class="text-slate-300"><?= sanitize($c['company'] ?? '—') ?></div>
                        <div class="text-slate-500 text-xs"><?= sanitize($c['industry'] ?? '') ?></div>
                    </td>
                    <td class="text-slate-300 text-xs max-w-[150px] truncate"><?= sanitize($c['service_interest'] ?? '—') ?></td>
                    <td class="text-slate-300 text-xs"><?= $c['preferred_date'] ? formatDate($c['preferred_date']) : '—' ?><br><?= sanitize($c['preferred_time'] ?? '') ?></td>
                    <td><span class="text-slate-300 text-xs capitalize"><?= str_replace('_', ' ', $c['meeting_type']) ?></span></td>
                    <td><span class="badge-status status-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            <a href="?id=<?= $c['id'] ?>&status=confirmed" class="admin-btn admin-btn-sm" style="background:#059669;font-size:.7rem;padding:4px 8px;">Confirm</a>
                            <a href="?id=<?= $c['id'] ?>&status=completed" class="admin-btn admin-btn-sm" style="background:#7c3aed;font-size:.7rem;padding:4px 8px;">Done</a>
                            <a href="?id=<?= $c['id'] ?>&status=cancelled" class="admin-btn admin-btn-danger admin-btn-sm" style="font-size:.7rem;padding:4px 8px;">Cancel</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
