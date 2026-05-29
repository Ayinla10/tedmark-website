<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$adminTitle = 'Testimonials';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = (int)($_POST['t_id'] ?? 0);
    $data = ['client_name'=>trim($_POST['client_name']??''),'client_title'=>trim($_POST['client_title']??''),'company'=>trim($_POST['company']??''),'industry'=>trim($_POST['industry']??''),'content'=>trim($_POST['content']??''),'rating'=>(int)($_POST['rating']??5),'status'=>$_POST['status']??'active','featured'=>(int)isset($_POST['featured']),'sort_order'=>(int)($_POST['sort_order']??0)];
    if ($data['client_name'] && $data['content']) {
        if (!empty($_FILES['avatar']['name'])) { $av = uploadFile($_FILES['avatar'], 'testimonials'); if ($av) $data['avatar'] = $av; }
        if ($tid) { update('testimonials',$data,'id=?',[$tid]); flash('success','Updated.'); } else { insert('testimonials',$data); flash('success','Added.'); }
        redirect(SITE_URL.'/admin/testimonials.php');
    }
}
if (isset($_GET['delete'])) { query("DELETE FROM testimonials WHERE id=?",[(int)$_GET['delete']]); flash('success','Deleted.'); redirect(SITE_URL.'/admin/testimonials.php'); }

$testimonials = fetchAll("SELECT * FROM testimonials ORDER BY featured DESC, sort_order ASC, created_at DESC");
$edit = $id ? fetchOne("SELECT * FROM testimonials WHERE id=?",[$id]) : null;
require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($action !== 'list'): ?>
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="/tedmark-digital/admin/testimonials.php" class="admin-btn admin-btn-outline text-slate-400">← Back</a>
        <h2 class="text-white font-bold text-xl"><?= $edit ? 'Edit' : 'Add' ?> Testimonial</h2>
    </div>
    <form method="POST" enctype="multipart/form-data" class="admin-card space-y-4">
        <input type="hidden" name="t_id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Client Name *</label><input type="text" name="client_name" class="admin-input" required value="<?= sanitize($edit['client_name'] ?? '') ?>"></div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Job Title</label><input type="text" name="client_title" class="admin-input" value="<?= sanitize($edit['client_title'] ?? '') ?>"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Company</label><input type="text" name="company" class="admin-input" value="<?= sanitize($edit['company'] ?? '') ?>"></div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Industry</label><input type="text" name="industry" class="admin-input" value="<?= sanitize($edit['industry'] ?? '') ?>"></div>
        </div>
        <div><label class="block text-slate-300 text-sm font-semibold mb-2">Testimonial *</label><textarea name="content" class="admin-input" rows="4" required><?= sanitize($edit['content'] ?? '') ?></textarea></div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Rating</label><select name="rating" class="admin-input"><?php for($r=5;$r>=1;$r--): ?><option value="<?=$r?>" <?=($edit['rating']??5)==$r?'selected':''?>><?=$r?> Stars</option><?php endfor; ?></select></div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Status</label><select name="status" class="admin-input"><option value="active" <?=($edit['status']??'active')==='active'?'selected':''?>>Active</option><option value="inactive" <?=($edit['status']??'')==='inactive'?'selected':''?>>Inactive</option></select></div>
            <div><label class="block text-slate-300 text-sm font-semibold mb-2">Sort Order</label><input type="number" name="sort_order" class="admin-input" value="<?=$edit['sort_order']??0?>"></div>
        </div>
        <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="featured" class="w-4 h-4 rounded" <?=!empty($edit['featured'])?'checked':''?>><span class="text-slate-300 text-sm">Feature on homepage</span></label>
        <div><label class="block text-slate-300 text-sm font-semibold mb-2">Avatar Photo</label><input type="file" name="avatar" class="admin-input" accept="image/*"></div>
        <div class="flex gap-4"><button type="submit" class="admin-btn"><?=$edit?'Update':'Add'?> Testimonial</button><a href="/tedmark-digital/admin/testimonials.php" class="admin-btn admin-btn-outline text-slate-400">Cancel</a></div>
    </form>
</div>
<?php else: ?>
<div class="space-y-5">
    <div class="flex justify-between items-center">
        <p class="text-slate-400"><?= count($testimonials) ?> testimonials</p>
        <a href="?action=new" class="admin-btn">+ Add Testimonial</a>
    </div>
    <div class="grid md:grid-cols-2 gap-5">
        <?php foreach ($testimonials as $t): ?>
        <div class="admin-card space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-brand-500 flex items-center justify-center text-white font-bold"><?= strtoupper(substr($t['client_name'],0,1)) ?></div>
                    <div>
                        <div class="text-white font-bold text-sm"><?= sanitize($t['client_name']) ?></div>
                        <div class="text-slate-400 text-xs"><?= sanitize($t['client_title']??'') ?><?= $t['company'] ? ' · '.sanitize($t['company']) : '' ?></div>
                    </div>
                </div>
                <div class="flex gap-1 text-amber-400 text-xs"><?= str_repeat('★',$t['rating']) ?><?= str_repeat('☆',5-$t['rating']) ?></div>
            </div>
            <p class="text-slate-300 text-sm leading-relaxed italic line-clamp-3">"<?= sanitize($t['content']) ?>"</p>
            <div class="flex items-center justify-between">
                <div class="flex gap-2">
                    <?php if ($t['featured']): ?><span class="badge-status status-confirmed text-xs">Featured</span><?php endif; ?>
                    <span class="badge-status status-<?= $t['status']==='active'?'confirmed':'draft' ?> text-xs"><?= ucfirst($t['status']) ?></span>
                </div>
                <div class="flex gap-2">
                    <a href="?action=edit&id=<?= $t['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm text-slate-300">Edit</a>
                    <a href="?delete=<?= $t['id'] ?>" onclick="return confirm('Delete?')" class="admin-btn admin-btn-danger admin-btn-sm">Del</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($testimonials)): ?><div class="admin-card col-span-2 text-center py-10 text-slate-500">No testimonials yet. <a href="?action=new" class="text-brand-400">Add one →</a></div><?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
