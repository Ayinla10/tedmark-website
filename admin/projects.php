<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$adminTitle = 'Portfolio Projects';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['project_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'websites';
    $client = trim($_POST['client_name'] ?? '');
    $industry = trim($_POST['client_industry'] ?? '');
    $technologies = array_filter(array_map('trim', explode(',', $_POST['technologies'] ?? '')));
    $results = trim($_POST['results'] ?? '');
    $project_url = trim($_POST['project_url'] ?? '');
    $status = in_array($_POST['status'] ?? 'active', ['active','draft','archived']) ? $_POST['status'] : 'active';
    $featured = (int)isset($_POST['featured']);
    $slug = slugify($title);

    $thumb = null;
    if (!empty($_FILES['thumbnail']['name'])) $thumb = uploadFile($_FILES['thumbnail'], 'projects');

    if ($title) {
        $data = ['title'=>$title,'slug'=>$slug,'description'=>$description,'category'=>$category,'client_name'=>$client,'client_industry'=>$industry,'technologies'=>json_encode($technologies),'results'=>$results,'project_url'=>$project_url,'status'=>$status,'featured'=>$featured];
        if ($thumb) $data['thumbnail'] = $thumb;
        if ($pid) { update('projects', $data, 'id=?', [$pid]); flash('success','Project updated.'); }
        else { insert('projects', $data); flash('success','Project added.'); }
        redirect(SITE_URL.'/admin/projects.php');
    } else { $msg = 'Title required.'; }
}

if (isset($_GET['delete'])) { query("DELETE FROM projects WHERE id=?", [(int)$_GET['delete']]); flash('success','Deleted.'); redirect(SITE_URL.'/admin/projects.php'); }
if (isset($_GET['toggle'])) { $p = fetchOne("SELECT featured FROM projects WHERE id=?", [(int)$_GET['toggle']]); update('projects', ['featured'=>$p ? (1-$p['featured']) : 0], 'id=?', [(int)$_GET['toggle']]); redirect(SITE_URL.'/admin/projects.php'); }

$projects = fetchAll("SELECT * FROM projects ORDER BY featured DESC, sort_order ASC, created_at DESC");
$editProject = $id ? fetchOne("SELECT * FROM projects WHERE id=?", [$id]) : null;
require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($action !== 'list'): ?>
<div class="max-w-3xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="/tedmark-digital/admin/projects.php" class="admin-btn admin-btn-outline text-slate-400">← Back</a>
        <h2 class="text-white font-bold text-xl"><?= $action==='edit' ? 'Edit Project' : 'Add New Project' ?></h2>
    </div>
    <?php if ($msg): ?><div class="p-4 bg-red-500/15 text-red-400 rounded-xl text-sm"><?= sanitize($msg) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="project_id" value="<?= $editProject['id'] ?? 0 ?>">
        <div class="admin-card space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <div><label class="block text-slate-300 text-sm font-semibold mb-2">Project Title *</label><input type="text" name="title" class="admin-input" required value="<?= sanitize($editProject['title'] ?? '') ?>"></div>
                <div><label class="block text-slate-300 text-sm font-semibold mb-2">Category</label>
                    <select name="category" class="admin-input">
                        <?php foreach (['websites','systems','automation','ecommerce','branding'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($editProject['category'] ?? 'websites') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Description</label><textarea name="description" class="admin-input" rows="3"><?= sanitize($editProject['description'] ?? '') ?></textarea></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div><label class="block text-slate-300 text-sm font-semibold mb-2">Client Name</label><input type="text" name="client_name" class="admin-input" value="<?= sanitize($editProject['client_name'] ?? '') ?>"></div>
                <div><label class="block text-slate-300 text-sm font-semibold mb-2">Client Industry</label><input type="text" name="client_industry" class="admin-input" value="<?= sanitize($editProject['client_industry'] ?? '') ?>"></div>
            </div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Technologies (comma separated)</label><input type="text" name="technologies" class="admin-input" value="<?= sanitize(implode(', ', json_decode($editProject['technologies'] ?? '[]', true))) ?>" placeholder="PHP, MySQL, Tailwind"></div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Results / Impact</label><input type="text" name="results" class="admin-input" value="<?= sanitize($editProject['results'] ?? '') ?>" placeholder="e.g. 40% revenue increase"></div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Project URL</label><input type="url" name="project_url" class="admin-input" value="<?= sanitize($editProject['project_url'] ?? '') ?>"></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div><label class="block text-slate-300 text-sm font-semibold mb-2">Status</label>
                    <select name="status" class="admin-input">
                        <?php foreach (['active','draft','archived'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($editProject['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex flex-col justify-end"><label class="flex items-center gap-3 cursor-pointer mt-4"><input type="checkbox" name="featured" class="w-4 h-4 rounded" <?= !empty($editProject['featured']) ? 'checked' : '' ?>><span class="text-slate-300 text-sm">Feature on homepage</span></label></div>
            </div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Thumbnail</label><?php if (!empty($editProject['thumbnail'])): ?><img src="<?= UPLOAD_URL.$editProject['thumbnail'] ?>" class="w-32 h-20 object-cover rounded-xl mb-3"><?php endif; ?><input type="file" name="thumbnail" class="admin-input" accept="image/*"></div>
        </div>
        <div class="flex gap-4"><button type="submit" class="admin-btn"><?= $editProject ? 'Update' : 'Add Project' ?></button><a href="/tedmark-digital/admin/projects.php" class="admin-btn admin-btn-outline text-slate-400">Cancel</a></div>
    </form>
</div>
<?php else: ?>
<div class="space-y-5">
    <div class="flex justify-between items-center">
        <p class="text-slate-400"><?= count($projects) ?> projects</p>
        <a href="?action=new" class="admin-btn">+ Add Project</a>
    </div>
    <div class="admin-card overflow-auto">
        <table class="admin-table">
            <thead><tr><th>Project</th><th>Category</th><th>Client</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($projects)): ?><tr><td colspan="6" class="text-center text-slate-500 py-10">No projects yet. <a href="?action=new" class="text-brand-400">Add one →</a></td></tr>
                <?php else: foreach ($projects as $p): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <?php if ($p['thumbnail']): ?><img src="<?= UPLOAD_URL.$p['thumbnail'] ?>" class="w-10 h-10 rounded-lg object-cover"><?php else: ?><div class="w-10 h-10 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-400">🚀</div><?php endif; ?>
                            <div><div class="text-white font-semibold text-sm"><?= sanitize($p['title']) ?></div></div>
                        </div>
                    </td>
                    <td><span class="badge-status status-published text-xs"><?= ucfirst($p['category']) ?></span></td>
                    <td class="text-slate-300 text-sm"><?= sanitize($p['client_name'] ?? '—') ?></td>
                    <td><a href="?toggle=<?= $p['id'] ?>" class="text-sm <?= $p['featured'] ? 'text-amber-400' : 'text-slate-500' ?> hover:text-amber-300"><?= $p['featured'] ? '⭐ Yes' : '☆ No' ?></a></td>
                    <td><span class="badge-status status-<?= $p['status'] === 'active' ? 'published' : 'draft' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><div class="flex gap-2"><a href="?action=edit&id=<?= $p['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm text-slate-300">Edit</a><a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete?')" class="admin-btn admin-btn-danger admin-btn-sm">Del</a></div></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
