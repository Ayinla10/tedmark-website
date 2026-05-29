<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$adminTitle = 'Blog Posts';
$msg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
    $status = in_array($_POST['status'] ?? '', ['draft','published','archived']) ? $_POST['status'] : 'draft';
    $featured = (int)isset($_POST['featured']);
    $seo_title = trim($_POST['seo_title'] ?? '');
    $seo_description = trim($_POST['seo_description'] ?? '');
    $slug = slugify($title);

    $image = null;
    if (!empty($_FILES['featured_image']['name'])) {
        $image = uploadFile($_FILES['featured_image'], 'blog');
    }

    if ($title && $content) {
        $data = compact('title','slug','content','excerpt','category_id','status','featured','seo_title','seo_description') + ['author_id' => currentAdmin()['id']];
        if ($image) $data['featured_image'] = $image;
        if ($status === 'published') $data['published_at'] = date('Y-m-d H:i:s');

        if ($postId) {
            update('blog_posts', $data, 'id = ?', [$postId]);
            flash('success', 'Post updated successfully.');
        } else {
            insert('blog_posts', $data);
            flash('success', 'Post created successfully.');
        }
        redirect(SITE_URL . '/admin/posts.php');
    } else {
        $msg = 'Title and content are required.';
    }
}

if ($_GET['delete'] ?? false) {
    $delId = (int)$_GET['delete'];
    query("DELETE FROM blog_posts WHERE id = ?", [$delId]);
    flash('success', 'Post deleted.');
    redirect(SITE_URL . '/admin/posts.php');
}

$posts = fetchAll("SELECT p.*, c.name as cat_name FROM blog_posts p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.created_at DESC");
$categories = fetchAll("SELECT * FROM categories ORDER BY name");
$editPost = $id ? fetchOne("SELECT * FROM blog_posts WHERE id = ?", [$id]) : null;

require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($action === 'new' || ($action === 'edit' && $editPost)): ?>
<div class="max-w-4xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="/tedmark-digital/admin/posts.php" class="admin-btn admin-btn-outline text-slate-400">← Back</a>
        <h2 class="text-white font-bold text-xl"><?= $action === 'edit' ? 'Edit Post' : 'New Blog Post' ?></h2>
    </div>

    <?php if ($msg): ?><div class="p-4 bg-red-500/15 text-red-400 rounded-xl text-sm"><?= sanitize($msg) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="post_id" value="<?= $editPost['id'] ?? 0 ?>">

        <div class="admin-card space-y-5">
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Post Title *</label>
                <input type="text" name="title" class="admin-input text-lg font-bold" placeholder="Enter post title..." value="<?= sanitize($editPost['title'] ?? '') ?>" required>
            </div>
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Excerpt / Summary</label>
                <textarea name="excerpt" class="admin-input" rows="3" placeholder="A short description shown in lists..."><?= sanitize($editPost['excerpt'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Content *</label>
                <textarea name="content" class="admin-input" rows="16" placeholder="Write your full post content here (HTML supported)..."><?= htmlspecialchars($editPost['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="admin-card space-y-4">
                <h3 class="text-white font-bold">Settings</h3>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Category</label>
                    <select name="category_id" class="admin-input">
                        <option value="">No category</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($editPost['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Status</label>
                    <select name="status" class="admin-input">
                        <option value="draft" <?= ($editPost['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($editPost['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= ($editPost['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="featured" class="w-4 h-4 rounded" <?= !empty($editPost['featured']) ? 'checked' : '' ?>>
                    <span class="text-slate-300 text-sm">Feature this post on homepage</span>
                </label>
            </div>

            <div class="admin-card space-y-4">
                <h3 class="text-white font-bold">Featured Image</h3>
                <?php if (!empty($editPost['featured_image'])): ?>
                <img src="<?= UPLOAD_URL . $editPost['featured_image'] ?>" class="w-full h-32 object-cover rounded-xl mb-3">
                <?php endif; ?>
                <input type="file" name="featured_image" class="admin-input text-slate-300" accept="image/*">

                <h3 class="text-white font-bold pt-2">SEO</h3>
                <input type="text" name="seo_title" class="admin-input" placeholder="SEO Title" value="<?= sanitize($editPost['seo_title'] ?? '') ?>">
                <textarea name="seo_description" class="admin-input" rows="2" placeholder="Meta description (150 chars)"><?= sanitize($editPost['seo_description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="admin-btn px-8 py-3">
                <?= $editPost ? 'Update Post' : 'Publish Post' ?>
            </button>
            <a href="/tedmark-digital/admin/posts.php" class="admin-btn admin-btn-outline text-slate-400">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>

<div class="space-y-5">
    <div class="flex items-center justify-between">
        <p class="text-slate-400"><?= count($posts) ?> posts total</p>
        <a href="/tedmark-digital/admin/posts.php?action=new" class="admin-btn">+ New Post</a>
    </div>

    <div class="admin-card overflow-hidden">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                <tr><td colspan="6" class="text-center text-slate-500 py-10">No posts yet. <a href="?action=new" class="text-brand-400 hover:underline">Create your first post →</a></td></tr>
                <?php else: ?>
                <?php foreach ($posts as $p): ?>
                <tr>
                    <td>
                        <div class="font-semibold text-white"><?= sanitize($p['title']) ?></div>
                        <div class="text-slate-500 text-xs"><?= sanitize($p['slug']) ?></div>
                    </td>
                    <td><?= sanitize($p['cat_name'] ?? '—') ?></td>
                    <td><span class="badge-status status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><?= number_format($p['views']) ?></td>
                    <td class="text-xs"><?= formatDate($p['created_at']) ?></td>
                    <td>
                        <div class="flex gap-2">
                            <a href="?action=edit&id=<?= $p['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm text-slate-300">Edit</a>
                            <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this post?')" class="admin-btn admin-btn-danger admin-btn-sm">Del</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
