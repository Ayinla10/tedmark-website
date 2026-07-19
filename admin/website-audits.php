<?php
$pageTitle = 'Website Audits';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM website_audits WHERE id=?", [(int)$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/website-audits.php'); exit;
}

$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

try {
    $totalCount = (int)(fetchOne("SELECT COUNT(*) AS c FROM website_audits WHERE unlocked=1")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($totalCount / $perPage));
    $audits = fetchAll("SELECT * FROM website_audits WHERE unlocked=1 ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
} catch(Exception $e) { $audits = []; $totalCount = 0; $totalPages = 1; }

require_once __DIR__ . '/includes/admin-layout.php';
?>

<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">Website Audit Leads <span class="badge badge-gray" style="margin-left:8px;"><?= $totalCount ?></span></span>
  </div>
  <table class="tm-table">
    <thead><tr>
      <th>Email</th><th>Name</th><th>Site Audited</th><th>Score</th><th>Date</th><th>Actions</th>
    </tr></thead>
    <tbody>
      <?php if (empty($audits)): ?>
      <tr><td colspan="6" style="text-align:center;color:#64748b;padding:40px 0;">No unlocked audits yet.</td></tr>
      <?php else: foreach ($audits as $a): ?>
      <tr>
        <td><a href="mailto:<?= htmlspecialchars($a['email']) ?>" style="color:#22c55e;text-decoration:none;"><?= htmlspecialchars($a['email']) ?></a></td>
        <td style="color:#e2e8f0;"><?= htmlspecialchars($a['name'] ?: 'N/A') ?></td>
        <td style="color:#64748b;font-size:0.8rem;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($a['target_url']) ?></td>
        <td><span class="badge <?= $a['score']>=80?'badge-green':($a['score']>=55?'badge-amber':'badge-red') ?>"><?= (int)$a['score'] ?>/100</span></td>
        <td style="color:#64748b;font-size:0.78rem;white-space:nowrap;"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
        <td><a href="?delete=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:20px;">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
  <a href="?page=<?= $p ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
