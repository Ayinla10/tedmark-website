<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin-layout.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM messages WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/messages.php'); exit;
}
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    query("UPDATE messages SET status='read' WHERE id = ?", [$_GET['read']]);
    header('Location: ' . SITE_URL . '/admin/messages.php'); exit;
}

$messages = fetchAll("SELECT * FROM messages ORDER BY created_at DESC");
$unread = array_filter($messages, fn($m) => $m['status'] === 'unread');
?>

<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">Inbox
      <?php if (count($unread)): ?>
      <span class="badge badge-red" style="margin-left:8px;"><?= count($unread) ?> unread</span>
      <?php endif; ?>
    </span>
  </div>

  <?php if (empty($messages)): ?>
  <div style="text-align:center;padding:48px 0;color:#64748b;">
    <i class="fa-solid fa-envelope" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.4;"></i>
    No messages yet.
  </div>
  <?php else: ?>
  <table class="tm-table">
    <thead><tr><th>From</th><th>Subject / Message</th><th>Service</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($messages as $m): ?>
    <tr style="<?= $m['status']==='unread' ? 'background:rgba(34,197,94,0.04);' : '' ?>">
      <td>
        <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($m['name']) ?> <?= $m['status']==='unread' ? '<span class="badge badge-red" style="font-size:0.6rem;">NEW</span>' : '' ?></div>
        <div style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($m['email']) ?></div>
        <?php if ($m['phone']): ?><div style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($m['phone']) ?></div><?php endif; ?>
      </td>
      <td style="max-width:340px;">
        <?php if ($m['subject']): ?><div style="font-weight:600;color:#e2e8f0;font-size:0.85rem;"><?= htmlspecialchars($m['subject']) ?></div><?php endif; ?>
        <div style="color:#94a3b8;font-size:0.82rem;line-height:1.5;"><?= htmlspecialchars(substr($m['message'], 0, 120)) ?>...</div>
      </td>
      <td><?php if ($m['service']): ?><span class="badge badge-blue"><?= htmlspecialchars($m['service']) ?></span><?php else: echo '—'; endif; ?></td>
      <td style="color:#64748b;font-size:0.78rem;white-space:nowrap;"><?= date('M j, Y', strtotime($m['created_at'])) ?></td>
      <td>
        <div class="gap-8">
          <?php if ($m['status']==='unread'): ?>
          <a href="?read=<?= $m['id'] ?>" class="btn btn-ghost btn-sm" title="Mark as read"><i class="fa-solid fa-check"></i></a>
          <?php endif; ?>
          <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= htmlspecialchars($m['subject']??'Your enquiry') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-reply"></i> Reply</a>
          <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this message?')"><i class="fa-solid fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
