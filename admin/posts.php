<?php
$pageTitle = 'Blog Posts';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    query("DELETE FROM posts WHERE id = ?", [$_GET['delete']]);
    header('Location: ' . SITE_URL . '/admin/posts.php?msg=deleted');
    exit;
}
// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $post = fetchOne("SELECT status FROM posts WHERE id = ?", [$_GET['toggle']]);
    if ($post) {
        $newStatus = $post['status'] === 'published' ? 'draft' : 'published';
        $pub = $newStatus === 'published' ? date('Y-m-d H:i:s') : null;
        query("UPDATE posts SET status = ?, published_at = ? WHERE id = ?", [$newStatus, $pub, $_GET['toggle']]);
    }
    header('Location: ' . SITE_URL . '/admin/posts.php');
    exit;
}

$posts = fetchAll("SELECT * FROM posts ORDER BY created_at DESC");
$msg = $_GET['msg'] ?? '';
require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($msg === 'saved'): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> Post saved successfully!</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="alert alert-error"><i class="fa-solid fa-trash"></i> Post deleted.</div><?php endif; ?>

<div class="tm-card">
  <div class="tm-card-header">
    <span class="tm-card-title">All Blog Posts <span class="badge badge-gray" style="margin-left:8px;"><?= count($posts) ?></span></span>
    <a href="<?= SITE_URL ?>/admin/post-edit.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Post</a>
  </div>

  <?php if (empty($posts)): ?>
  <div style="text-align:center;padding:48px 0;color:#64748b;">
    <i class="fa-solid fa-newspaper" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.4;"></i>
    No posts yet. <a href="<?= SITE_URL ?>/admin/post-edit.php" style="color:#22c55e;">Write your first post</a>
  </div>
  <?php else: ?>
  <table class="tm-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Category</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
    <tr>
      <td>
        <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($p['title']) ?></div>
        <div style="font-size:0.75rem;color:#64748b;">/blog/<?= htmlspecialchars($p['slug']) ?></div>
      </td>
      <td><span class="badge badge-blue"><?= htmlspecialchars($p['category'] ?? 'General') ?></span></td>
      <td>
        <a href="?toggle=<?= $p['id'] ?>" style="text-decoration:none;" title="Click to toggle">
          <span class="badge <?= $p['status'] === 'published' ? 'badge-green' : 'badge-amber' ?>">
            <?= ucfirst($p['status']) ?>
          </span>
        </a>
      </td>
      <td style="color:#64748b;font-size:0.8rem;"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
      <td>
        <div class="gap-8">
          <a href="<?= SITE_URL ?>/admin/post-edit.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
          <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= $p['slug'] ?>" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-eye"></i></a>
          <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?')"><i class="fa-solid fa-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
