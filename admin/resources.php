<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$adminTitle = 'Resources';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid = (int)($_POST['r_id'] ?? 0);
    $data = ['title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),'category'=>trim($_POST['category']??''),'status'=>$_POST['status']??'active','featured'=>(int)isset($_POST['featured'])];
    if ($data['title']) {
        if (!empty($_FILES['resource_file']['name'])) { $f = uploadFile($_FILES['resource_file'],'resources',['pdf','xlsx','docx','pptx','zip']); if ($f) { $data['file_path']=$f; $data['file_type']=pathinfo($f,PATHINFO_EXTENSION); $data['file_size']=round($_FILES['resource_file']['size']/1024).'KB'; } }
        if ($rid) { update('resources',$data,'id=?',[$rid]); flash('success','Updated.'); } else { insert('resources',$data); flash('success','Added.'); }
        redirect(SITE_URL.'/admin/resources.php');
    }
}
if (isset($_GET['delete'])) { query("DELETE FROM resources WHERE id=?",[(int)$_GET['delete']]); flash('success','Deleted.'); redirect(SITE_URL.'/admin/resources.php'); }

$resources = fetchAll("SELECT * FROM resources ORDER BY featured DESC, created_at DESC");
$edit = $id ? fetchOne("SELECT * FROM resources WHERE id=?",[$id]) : null;
require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($action !== 'list'): ?>
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="/tedmark-digital/admin/resources.php" class="admin-btn admin-btn-outline text-slate-400">← Back</a>
        <h2 class="text-white font-bold text-xl"><?=$edit?'Edit':'Upload New'?> Resource</h2>
    </div>
    <form method="POST" enctype="multipart/form-data" class="admin-card space-y-4">
        <input type="hidden" name="r_id" value="<?=$edit['id']??0?>">
        <div><label class="block text-slate-300 text-sm font-semibold mb-2">Resource Title *</label><input type="text" name="title" class="admin-input" required value="<?=sanitize($edit['title']??'')?>"></div>
        <div><label class="block text-slate-300 text-sm font-semibold mb-2">Description</label><textarea name="description" class="admin-input" rows="3"><?=sanitize($edit['description']??'')?></textarea></div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Category</label><input type="text" name="category" class="admin-input" placeholder="e.g. Templates, Guides" value="<?=sanitize($edit['category']??'')?>"></div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Status</label><select name="status" class="admin-input"><option value="active" <?=($edit['status']??'active')==='active'?'selected':''?>>Active</option><option value="draft" <?=($edit['status']??'')==='draft'?'selected':''?>>Draft</option></select></div>
        </div>
        <div><label class="block text-slate-300 text-sm font-semibold mb-2">File Upload (PDF, Excel, Word, etc.)</label><?php if (!empty($edit['file_path'])): ?><div class="text-slate-400 text-sm mb-2">Current: <?=sanitize($edit['file_path'])?></div><?php endif; ?><input type="file" name="resource_file" class="admin-input" accept=".pdf,.xlsx,.docx,.pptx,.zip"></div>
        <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="featured" class="w-4 h-4 rounded" <?=!empty($edit['featured'])?'checked':''?>><span class="text-slate-300 text-sm">Feature this resource</span></label>
        <div class="flex gap-4"><button type="submit" class="admin-btn"><?=$edit?'Update':'Upload'?></button><a href="/tedmark-digital/admin/resources.php" class="admin-btn admin-btn-outline text-slate-400">Cancel</a></div>
    </form>
</div>
<?php else: ?>
<div class="space-y-5">
    <div class="flex justify-between items-center">
        <p class="text-slate-400"><?=count($resources)?> resources</p>
        <a href="?action=new" class="admin-btn">+ Upload Resource</a>
    </div>
    <div class="admin-card overflow-auto">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Category</th><th>Type</th><th>Downloads</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($resources)): ?><tr><td colspan="6" class="text-center text-slate-500 py-10">No resources yet. <a href="?action=new" class="text-brand-400">Upload one →</a></td></tr>
                <?php else: foreach ($resources as $r): ?>
                <tr>
                    <td><div class="text-white font-semibold text-sm"><?=sanitize($r['title'])?></div><?php if ($r['featured']): ?><span class="text-amber-400 text-xs">⭐ Featured</span><?php endif; ?></td>
                    <td class="text-slate-300"><?=sanitize($r['category']??'—')?></td>
                    <td><span class="px-2 py-1 bg-white/8 rounded font-mono text-xs text-slate-400"><?=strtoupper($r['file_type']??'PDF')?></span></td>
                    <td class="text-slate-300"><?=number_format($r['download_count'])?></td>
                    <td><span class="badge-status status-<?=$r['status']==='active'?'published':'draft'?>"><?=ucfirst($r['status'])?></span></td>
                    <td><div class="flex gap-2"><a href="?action=edit&id=<?=$r['id']?>" class="admin-btn admin-btn-outline admin-btn-sm text-slate-300">Edit</a><a href="?delete=<?=$r['id']?>" onclick="return confirm('Delete?')" class="admin-btn admin-btn-danger admin-btn-sm">Del</a></div></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
