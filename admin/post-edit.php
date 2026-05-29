<?php
$pageTitle = 'Edit Post';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin-layout.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$post = $id ? fetchOne("SELECT * FROM posts WHERE id = ?", [$id]) : null;
$pageTitle = $post ? 'Edit Post' : 'New Post';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $slug     = trim($_POST['slug'] ?? '');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $body     = $_POST['body'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $tags     = trim($_POST['tags'] ?? '');
    $featured = trim($_POST['featured_image'] ?? '');
    $audio    = trim($_POST['audio_url'] ?? '');
    $hasAudio = isset($_POST['has_audio']) ? 1 : 0;
    $status   = $_POST['status'] ?? 'draft';
    $readTime = (int)($_POST['read_time'] ?? 5);

    if (!$title) {
        $error = 'Title is required.';
    } else {
        if (!$slug) $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $pub = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $data = compact('title','slug','excerpt','body','category','tags','featured_image','audio_url','has_audio','status','read_time');
        $data = [
            'title' => $title, 'slug' => $slug, 'excerpt' => $excerpt, 'body' => $body,
            'category' => $category, 'tags' => $tags, 'featured_image' => $featured,
            'audio_url' => $audio, 'has_audio' => $hasAudio, 'status' => $status,
            'read_time' => $readTime, 'published_at' => $pub,
        ];

        try {
            if ($post) {
                $set = implode(', ', array_map(fn($k) => "`$k`=?", array_keys($data)));
                query("UPDATE posts SET $set WHERE id = ?", [...array_values($data), $post['id']]);
                $id = $post['id'];
            } else {
                $set = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
                $ph  = implode(', ', array_fill(0, count($data), '?'));
                query("INSERT INTO posts ($set) VALUES ($ph)", array_values($data));
                $id = db()->lastInsertId();
            }
            header('Location: ' . SITE_URL . '/admin/post-edit.php?id=' . $id . '&msg=saved');
            exit;
        } catch (Exception $e) {
            $error = 'Error saving post: ' . $e->getMessage();
        }
    }
}

$msg = $_GET['msg'] ?? '';
$p = $post ?? [];
?>

<?php if ($msg === 'saved'): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> Post saved!</div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fa-solid fa-times"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

  <!-- LEFT: Main Content -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <div class="tm-card">
      <div class="form-group">
        <label>Post Title *</label>
        <input type="text" name="title" id="title-input" value="<?= htmlspecialchars($p['title'] ?? '') ?>" placeholder="Enter post title..." style="font-size:1.1rem;font-weight:600;" required>
      </div>
      <div class="form-group">
        <label>Slug (URL)</label>
        <input type="text" name="slug" id="slug-input" value="<?= htmlspecialchars($p['slug'] ?? '') ?>" placeholder="auto-generated-from-title">
        <small style="color:#475569;font-size:0.75rem;">new.tedmarkdigital.com/blog-post.php?slug=<span id="slug-preview"><?= htmlspecialchars($p['slug'] ?? 'your-slug') ?></span></small>
      </div>
      <div class="form-group">
        <label>Excerpt (shown in listings)</label>
        <textarea name="excerpt" rows="2" placeholder="Brief summary of the post..."><?= htmlspecialchars($p['excerpt'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- Rich Text Editor -->
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Post Content</span></div>
      <!-- Toolbar -->
      <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:12px;padding:8px;background:#0b1528;border-radius:8px;border:1px solid #1e293b;">
        <?php $tools = [
          ['cmd'=>'bold','icon'=>'fa-bold','title'=>'Bold'],
          ['cmd'=>'italic','icon'=>'fa-italic','title'=>'Italic'],
          ['cmd'=>'underline','icon'=>'fa-underline','title'=>'Underline'],
          ['sep'=>true],
          ['cmd'=>'h2','icon'=>'fa-heading','title'=>'Heading 2','tag'=>'h2'],
          ['cmd'=>'h3','icon'=>'fa-heading','title'=>'Heading 3','tag'=>'h3','small'=>true],
          ['sep'=>true],
          ['cmd'=>'insertUnorderedList','icon'=>'fa-list-ul','title'=>'Bullet List'],
          ['cmd'=>'insertOrderedList','icon'=>'fa-list-ol','title'=>'Numbered List'],
          ['sep'=>true],
          ['cmd'=>'createLink','icon'=>'fa-link','title'=>'Insert Link'],
          ['cmd'=>'blockquote','icon'=>'fa-quote-left','title'=>'Blockquote','tag'=>'blockquote'],
          ['sep'=>true],
          ['cmd'=>'undo','icon'=>'fa-rotate-left','title'=>'Undo'],
          ['cmd'=>'redo','icon'=>'fa-rotate-right','title'=>'Redo'],
        ];
        foreach ($tools as $t):
          if (!empty($t['sep'])): ?>
          <div style="width:1px;background:#1e293b;margin:0 4px;"></div>
          <?php else: ?>
          <button type="button" class="editor-btn" data-cmd="<?= $t['cmd'] ?>" <?= isset($t['tag']) ? 'data-tag="'.$t['tag'].'"' : '' ?> title="<?= $t['title'] ?>"
            style="padding:6px 9px;background:transparent;border:none;color:#94a3b8;cursor:pointer;border-radius:6px;font-size:<?= !empty($t['small']) ? '0.7rem' : '0.85rem' ?>;">
            <i class="fa-solid <?= $t['icon'] ?>"></i>
          </button>
          <?php endif;
        endforeach; ?>
      </div>
      <div id="editor" contenteditable="true" style="min-height:400px;background:#0b1528;border:1px solid #1e293b;border-radius:8px;padding:16px 20px;color:#e2e8f0;font-size:0.95rem;line-height:1.8;outline:none;"><?= $p['body'] ?? '' ?></div>
      <input type="hidden" name="body" id="body-input">
    </div>

    <!-- Audio -->
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title"><i class="fa-solid fa-headphones text-green" style="margin-right:8px;"></i>Audio (Optional)</span></div>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <input type="checkbox" name="has_audio" id="has_audio" <?= !empty($p['has_audio']) ? 'checked' : '' ?> style="width:auto;accent-color:#22c55e;">
        <label for="has_audio" style="text-transform:none;font-size:0.9rem;color:#e2e8f0;margin:0;">This post has an audio version</label>
      </div>
      <div class="form-group">
        <label>Audio File URL</label>
        <input type="url" name="audio_url" value="<?= htmlspecialchars($p['audio_url'] ?? '') ?>" placeholder="https://...">
      </div>
    </div>
  </div>

  <!-- RIGHT: Sidebar -->
  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px;">

    <!-- Publish -->
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Publish</span></div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="draft" <?= ($p['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="published" <?= ($p['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
      </div>
      <div class="form-group">
        <label>Read Time (minutes)</label>
        <input type="number" name="read_time" value="<?= htmlspecialchars($p['read_time'] ?? '5') ?>" min="1" max="60">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Save Post</button>
      <?php if ($post): ?>
      <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= $post['slug'] ?>" target="_blank" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:8px;"><i class="fa-solid fa-eye"></i> Preview</a>
      <?php endif; ?>
    </div>

    <!-- Category & Tags -->
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Category & Tags</span></div>
      <div class="form-group">
        <label>Category</label>
        <select name="category">
          <option value="">Select category</option>
          <?php foreach(['Business Systems','Automation','Web Development','E-Commerce','IT Consulting','Branding','Strategy'] as $cat): ?>
          <option value="<?= $cat ?>" <?= ($p['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Tags (comma separated)</label>
        <input type="text" name="tags" value="<?= htmlspecialchars($p['tags'] ?? '') ?>" placeholder="automation, systems, africa">
      </div>
    </div>

    <!-- Featured Image -->
    <div class="tm-card">
      <div class="tm-card-header"><span class="tm-card-title">Featured Image</span></div>
      <div class="form-group">
        <label>Image URL</label>
        <input type="url" name="featured_image" id="feat-img" value="<?= htmlspecialchars($p['featured_image'] ?? '') ?>" placeholder="https://images.unsplash.com/...">
      </div>
      <?php if (!empty($p['featured_image'])): ?>
      <img src="<?= htmlspecialchars($p['featured_image']) ?>" style="width:100%;height:120px;object-fit:cover;border-radius:8px;">
      <?php endif; ?>
    </div>

    <!-- Back -->
    <a href="<?= SITE_URL ?>/admin/posts.php" class="btn btn-ghost" style="justify-content:center;"><i class="fa-solid fa-arrow-left"></i> All Posts</a>
  </div>

</div>
</form>

<style>
#editor h2{font-size:1.3rem;font-weight:700;color:#fff;margin:20px 0 8px;}
#editor h3{font-size:1.1rem;font-weight:600;color:#e2e8f0;margin:16px 0 6px;}
#editor p{margin-bottom:12px;}
#editor blockquote{border-left:3px solid #22c55e;padding-left:16px;color:#94a3b8;margin:16px 0;}
#editor a{color:#22c55e;}
#editor ul,#editor ol{padding-left:24px;margin-bottom:12px;}
#editor li{margin-bottom:4px;}
.editor-btn:hover{background:#1e293b !important;color:#fff !important;}
</style>

<script>
// Auto slug from title
const titleInput = document.getElementById('title-input');
const slugInput  = document.getElementById('slug-input');
const slugPreview = document.getElementById('slug-preview');
titleInput.addEventListener('input', () => {
  if (!slugInput.dataset.manual) {
    const slug = titleInput.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
    slugInput.value = slug;
    slugPreview.textContent = slug || 'your-slug';
  }
});
slugInput.addEventListener('input', () => {
  slugInput.dataset.manual = '1';
  slugPreview.textContent = slugInput.value || 'your-slug';
});

// Editor toolbar
document.querySelectorAll('.editor-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const cmd = btn.dataset.cmd;
    const tag = btn.dataset.tag;
    if (tag === 'h2' || tag === 'h3') {
      document.execCommand('formatBlock', false, tag);
    } else if (tag === 'blockquote') {
      document.execCommand('formatBlock', false, 'blockquote');
    } else if (cmd === 'createLink') {
      const url = prompt('Enter URL:');
      if (url) document.execCommand('createLink', false, url);
    } else {
      document.execCommand(cmd, false, null);
    }
    document.getElementById('editor').focus();
  });
});

// Sync editor to hidden input on submit
document.querySelector('form').addEventListener('submit', () => {
  document.getElementById('body-input').value = document.getElementById('editor').innerHTML;
});
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
