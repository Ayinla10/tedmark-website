<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin-layout.php';

try {
    $stats = [
        'posts'        => fetchOne("SELECT COUNT(*) as n FROM posts WHERE status='published'")['n'] ?? 0,
        'drafts'       => fetchOne("SELECT COUNT(*) as n FROM posts WHERE status='draft'")['n'] ?? 0,
        'projects'     => fetchOne("SELECT COUNT(*) as n FROM projects WHERE status='active'")['n'] ?? 0,
        'messages'     => fetchOne("SELECT COUNT(*) as n FROM messages WHERE status='unread'")['n'] ?? 0,
        'testimonials' => fetchOne("SELECT COUNT(*) as n FROM testimonials WHERE status='active'")['n'] ?? 0,
        'services'     => fetchOne("SELECT COUNT(*) as n FROM services WHERE status='active'")['n'] ?? 0,
    ];
    $recentMessages = fetchAll("SELECT * FROM messages ORDER BY created_at DESC LIMIT 6");
    $recentPosts    = fetchAll("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");
} catch (Exception $e) {
    $stats = array_fill_keys(['posts','drafts','projects','messages','testimonials','services'], 0);
    $recentMessages = $recentPosts = [];
}

$admin = currentAdmin();
$firstName = explode(' ', $admin['name'] ?? 'Admin')[0];
?>

<!-- Welcome -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
  <div>
    <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.4rem;font-weight:800;color:#fff;">Welcome back, <?= htmlspecialchars($firstName) ?> 👋</h2>
    <p style="color:#64748b;font-size:0.85rem;margin-top:4px;"><?= date('l, F j Y') ?></p>
  </div>
  <div class="gap-8">
    <a href="<?= SITE_URL ?>/admin/post-edit.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Post</a>
    <a href="<?= SITE_URL ?>/admin/project-edit.php" class="btn btn-ghost btn-sm"><i class="fa-solid fa-plus"></i> New Project</a>
  </div>
</div>

<!-- Stat Cards -->
<div class="tm-grid-4" style="margin-bottom:28px;">
  <div class="tm-stat">
    <div class="tm-stat-icon" style="background:rgba(34,197,94,0.12);color:#22c55e;"><i class="fa-solid fa-newspaper"></i></div>
    <div>
      <div class="tm-stat-value"><?= $stats['posts'] ?></div>
      <div class="tm-stat-label">Published Posts</div>
    </div>
  </div>
  <div class="tm-stat">
    <div class="tm-stat-icon" style="background:rgba(96,165,250,0.12);color:#60a5fa;"><i class="fa-solid fa-folder-open"></i></div>
    <div>
      <div class="tm-stat-value"><?= $stats['projects'] ?></div>
      <div class="tm-stat-label">Active Projects</div>
    </div>
  </div>
  <div class="tm-stat">
    <div class="tm-stat-icon" style="background:rgba(244,63,94,0.12);color:#f43f5e;"><i class="fa-solid fa-envelope"></i></div>
    <div>
      <div class="tm-stat-value"><?= $stats['messages'] ?></div>
      <div class="tm-stat-label">Unread Messages</div>
    </div>
  </div>
  <div class="tm-stat">
    <div class="tm-stat-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;"><i class="fa-solid fa-star"></i></div>
    <div>
      <div class="tm-stat-value"><?= $stats['testimonials'] ?></div>
      <div class="tm-stat-label">Testimonials</div>
    </div>
  </div>
</div>

<!-- Recent Activity -->
<div class="tm-grid-2" style="margin-bottom:28px;">

  <!-- Recent Messages -->
  <div class="tm-card">
    <div class="tm-card-header">
      <span class="tm-card-title">Recent Messages</span>
      <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <?php if (empty($recentMessages)): ?>
    <p style="color:#64748b;text-align:center;padding:24px 0;font-size:0.85rem;">No messages yet</p>
    <?php else: ?>
    <?php foreach ($recentMessages as $m): ?>
    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #1e293b;">
      <div style="width:36px;height:36px;border-radius:8px;background:#0b1528;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:700;font-size:0.9rem;flex-shrink:0;">
        <?= strtoupper(substr($m['name'], 0, 1)) ?>
      </div>
      <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="color:#fff;font-weight:600;font-size:0.85rem;"><?= htmlspecialchars($m['name']) ?></span>
          <?php if ($m['status'] === 'unread'): ?><span class="badge badge-red" style="font-size:0.62rem;padding:2px 6px;">NEW</span><?php endif; ?>
        </div>
        <div style="color:#64748b;font-size:0.78rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars(substr($m['message'], 0, 55)) ?>...</div>
      </div>
      <span style="color:#475569;font-size:0.72rem;white-space:nowrap;"><?= date('M j', strtotime($m['created_at'])) ?></span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Recent Posts -->
  <div class="tm-card">
    <div class="tm-card-header">
      <span class="tm-card-title">Recent Blog Posts</span>
      <a href="<?= SITE_URL ?>/admin/posts.php" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <?php if (empty($recentPosts)): ?>
    <p style="color:#64748b;text-align:center;padding:24px 0;font-size:0.85rem;">No posts yet — <a href="<?= SITE_URL ?>/admin/post-edit.php" style="color:#22c55e;">write one</a></p>
    <?php else: ?>
    <?php foreach ($recentPosts as $p): ?>
    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #1e293b;">
      <div style="width:36px;height:36px;border-radius:8px;background:rgba(34,197,94,0.1);display:flex;align-items:center;justify-content:center;color:#22c55e;flex-shrink:0;">
        <i class="fa-solid fa-newspaper" style="font-size:0.8rem;"></i>
      </div>
      <div style="flex:1;min-width:0;">
        <div style="color:#fff;font-weight:600;font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['title']) ?></div>
        <div style="color:#64748b;font-size:0.78rem;"><?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?></div>
      </div>
      <span class="badge <?= $p['status'] === 'published' ? 'badge-green' : 'badge-amber' ?>"><?= ucfirst($p['status']) ?></span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Quick Actions -->
<div class="tm-card">
  <div class="tm-card-header"><span class="tm-card-title">Quick Actions</span></div>
  <div class="tm-grid-4">
    <?php $actions = [
      ['icon'=>'fa-newspaper',   'color'=>'#22c55e',  'bg'=>'rgba(34,197,94,0.1)',   'label'=>'New Blog Post',   'href'=>'post-edit.php'],
      ['icon'=>'fa-folder-open', 'color'=>'#60a5fa',  'bg'=>'rgba(96,165,250,0.1)',  'label'=>'New Project',     'href'=>'project-edit.php'],
      ['icon'=>'fa-house',       'color'=>'#f59e0b',  'bg'=>'rgba(245,158,11,0.1)',  'label'=>'Edit Homepage',   'href'=>'homepage.php'],
      ['icon'=>'fa-gear',        'color'=>'#a78bfa',  'bg'=>'rgba(167,139,250,0.1)', 'label'=>'Site Settings',   'href'=>'settings.php'],
    ]; foreach ($actions as $a): ?>
    <a href="<?= SITE_URL ?>/admin/<?= $a['href'] ?>" style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:20px;background:#0b1528;border:1px solid #1e293b;border-radius:10px;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='<?= $a['color'] ?>'" onmouseout="this.style.borderColor='#1e293b'">
      <div style="width:44px;height:44px;border-radius:10px;background:<?= $a['bg'] ?>;display:flex;align-items:center;justify-content:center;color:<?= $a['color'] ?>;font-size:1.1rem;">
        <i class="fa-solid <?= $a['icon'] ?>"></i>
      </div>
      <span style="color:#cbd5e1;font-size:0.82rem;font-weight:600;text-align:center;"><?= $a['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
