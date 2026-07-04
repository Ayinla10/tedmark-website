<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM messages WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/messages.php'); exit;
}
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    query("UPDATE messages SET status='read' WHERE id = ?", [$_GET['read']]);
    header('Location: ' . SITE_URL . '/admin/messages.php'); exit;
}

// Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_filter(array_map('intval', $_POST['ids']));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($_POST['bulk_action'] === 'delete') {
            query("DELETE FROM messages WHERE id IN ($placeholders)", $ids);
        } elseif ($_POST['bulk_action'] === 'read') {
            query("UPDATE messages SET status='read' WHERE id IN ($placeholders)", $ids);
        }
    }
    header('Location: ' . SITE_URL . '/admin/messages.php?page=' . (int)($_POST['current_page'] ?? 1)); exit;
}

// Viewing a single message — mark it read automatically
$viewing = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $viewing = fetchOne("SELECT * FROM messages WHERE id = ?", [$_GET['view']]);
    if ($viewing && $viewing['status'] === 'unread') {
        query("UPDATE messages SET status='read' WHERE id = ?", [$_GET['view']]);
        $viewing['status'] = 'read';
    }
}

// Pagination
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$totalCount = (int)(fetchOne("SELECT COUNT(*) AS c FROM messages")['c'] ?? 0);
$totalPages = max(1, (int)ceil($totalCount / $perPage));

$messages = fetchAll("SELECT * FROM messages ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$unread   = (int)(fetchOne("SELECT COUNT(*) AS c FROM messages WHERE status='unread'")['c'] ?? 0);

require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($viewing): ?>
<!-- ===== MESSAGE DETAIL VIEW ===== -->
<div class="tm-card" style="max-width:720px;">
  <div class="tm-card-header">
    <span class="tm-card-title">Message from <?= htmlspecialchars($viewing['name']) ?></span>
    <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Inbox</a>
  </div>

  <div style="padding:4px 0 20px;border-bottom:1px solid #1e293b;margin-bottom:20px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:12px;">
      <div>
        <div style="font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">From</div>
        <div style="color:#fff;font-weight:600;"><?= htmlspecialchars($viewing['name']) ?></div>
        <div style="color:#22c55e;font-size:0.85rem;"><?= htmlspecialchars($viewing['email']) ?></div>
        <?php if ($viewing['phone']): ?><div style="color:#94a3b8;font-size:0.85rem;"><?= htmlspecialchars($viewing['phone']) ?></div><?php endif; ?>
      </div>
      <div>
        <div style="font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Received</div>
        <div style="color:#e2e8f0;"><?= date('M j, Y \a\t g:i A', strtotime($viewing['created_at'])) ?></div>
        <?php if ($viewing['service']): ?><div style="margin-top:6px;"><span class="badge badge-blue"><?= htmlspecialchars($viewing['service']) ?></span></div><?php endif; ?>
      </div>
    </div>
    <?php if ($viewing['subject']): ?>
    <div style="margin-top:12px;">
      <div style="font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Subject</div>
      <div style="color:#fff;font-weight:600;"><?= htmlspecialchars($viewing['subject']) ?></div>
    </div>
    <?php endif; ?>
  </div>

  <div style="margin-bottom:24px;">
    <div style="font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Message</div>
    <div style="color:#e2e8f0;line-height:1.7;white-space:pre-wrap;background:#0b1528;border-radius:10px;padding:16px;"><?= htmlspecialchars($viewing['message']) ?></div>
  </div>

  <div class="gap-8">
    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($viewing['email']) ?>&su=<?= urlencode('Re: ' . ($viewing['subject'] ?: 'Your enquiry to Tedmark Digital')) ?>&body=<?= urlencode("Hi {$viewing['name']},\n\n") ?>" target="_blank" class="btn btn-primary">
      <i class="fa-brands fa-google"></i> Reply via Gmail
    </a>
    <button type="button" class="btn btn-ghost" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($viewing['email'], ENT_QUOTES) ?>'); this.innerHTML='<i class=\'fa-solid fa-check\'></i> Copied!'; setTimeout(()=>this.innerHTML='<i class=\'fa-solid fa-copy\'></i> Copy Email',2000);">
      <i class="fa-solid fa-copy"></i> Copy Email
    </button>
    <a href="?delete=<?= $viewing['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this message?')"><i class="fa-solid fa-trash"></i> Delete</a>
  </div>
</div>

<?php else: ?>
<!-- ===== INBOX LIST ===== -->
<form method="POST" id="bulk-form">
<input type="hidden" name="current_page" value="<?= $page ?>">
<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">Inbox
      <?php if ($unread): ?>
      <span class="badge badge-red" style="margin-left:8px;"><?= $unread ?> unread</span>
      <?php endif; ?>
      <span style="color:#64748b;font-weight:400;font-size:0.8rem;margin-left:10px;"><?= $totalCount ?> total</span>
    </span>
    <div class="gap-8">
      <button type="submit" name="bulk_action" value="read" class="btn btn-ghost btn-sm" onclick="return confirm('Mark selected as read?')"><i class="fa-solid fa-check"></i> Mark Read</button>
      <button type="submit" name="bulk_action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected messages?')"><i class="fa-solid fa-trash"></i> Delete Selected</button>
    </div>
  </div>

  <?php if (empty($messages)): ?>
  <div style="text-align:center;padding:48px 0;color:#64748b;">
    <i class="fa-solid fa-envelope" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.4;"></i>
    No messages yet.
  </div>
  <?php else: ?>
  <table class="tm-table">
    <thead><tr>
      <th style="width:32px;"><input type="checkbox" id="select-all"></th>
      <th>From</th><th>Subject / Message</th><th>Service</th><th>Received</th><th>Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($messages as $m): ?>
    <tr style="<?= $m['status']==='unread' ? 'background:rgba(34,197,94,0.04);' : '' ?>cursor:pointer;" onclick="window.location='?view=<?= $m['id'] ?>'">
      <td onclick="event.stopPropagation();"><input type="checkbox" name="ids[]" value="<?= $m['id'] ?>" class="row-check"></td>
      <td>
        <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($m['name']) ?> <?= $m['status']==='unread' ? '<span class="badge badge-red" style="font-size:0.6rem;">NEW</span>' : '' ?></div>
        <div style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($m['email']) ?></div>
        <?php if ($m['phone']): ?><div style="font-size:0.75rem;color:#64748b;"><?= htmlspecialchars($m['phone']) ?></div><?php endif; ?>
      </td>
      <td style="max-width:340px;">
        <?php if ($m['subject']): ?><div style="font-weight:600;color:#e2e8f0;font-size:0.85rem;"><?= htmlspecialchars($m['subject']) ?></div><?php endif; ?>
        <div style="color:#94a3b8;font-size:0.82rem;line-height:1.5;"><?= htmlspecialchars(substr($m['message'], 0, 120)) ?><?= strlen($m['message'])>120?'...':'' ?></div>
      </td>
      <td><?php if ($m['service']): ?><span class="badge badge-blue"><?= htmlspecialchars($m['service']) ?></span><?php else: echo '—'; endif; ?></td>
      <td style="color:#64748b;font-size:0.78rem;white-space:nowrap;"><?= date('M j, Y', strtotime($m['created_at'])) ?><br><span style="color:#475569;"><?= date('g:i A', strtotime($m['created_at'])) ?></span></td>
      <td onclick="event.stopPropagation();">
        <div class="gap-8">
          <a href="?view=<?= $m['id'] ?>" class="btn btn-ghost btn-sm" title="Read message"><i class="fa-solid fa-eye"></i></a>
          <?php if ($m['status']==='unread'): ?>
          <a href="?read=<?= $m['id'] ?>" class="btn btn-ghost btn-sm" title="Mark as read"><i class="fa-solid fa-check"></i></a>
          <?php endif; ?>
          <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this message?')"><i class="fa-solid fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
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
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
