<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();
$adminTitle = 'Messages';

if (isset($_GET['read'])) { update('messages', ['status'=>'read'], 'id=?', [(int)$_GET['read']]); redirect(SITE_URL.'/admin/messages.php'); }
if (isset($_GET['delete'])) { query("DELETE FROM messages WHERE id=?", [(int)$_GET['delete']]); flash('success','Message deleted.'); redirect(SITE_URL.'/admin/messages.php'); }

$view = $_GET['view'] ?? null;
$viewMsg = $view ? fetchOne("SELECT * FROM messages WHERE id=?", [(int)$view]) : null;
if ($viewMsg && $viewMsg['status'] === 'unread') update('messages', ['status'=>'read'], 'id=?', [$viewMsg['id']]);

$messages = fetchAll("SELECT * FROM messages ORDER BY created_at DESC");
require_once __DIR__ . '/includes/admin-layout.php';
?>

<div class="grid lg:grid-cols-5 gap-6">
    <!-- List -->
    <div class="lg:col-span-2 space-y-2">
        <?php if (empty($messages)): ?>
        <div class="admin-card text-center py-12 text-slate-500">No messages yet</div>
        <?php else: ?>
        <?php foreach ($messages as $m): ?>
        <a href="?view=<?= $m['id'] ?>" class="block p-4 rounded-xl border transition-all <?= $view == $m['id'] ? 'border-brand-500/40 bg-brand-500/5' : 'border-white/7 bg-white/3 hover:border-white/15' ?>">
            <div class="flex items-start justify-between gap-2 mb-1">
                <div class="flex items-center gap-2">
                    <?php if ($m['status'] === 'unread'): ?><div class="w-2 h-2 bg-red-400 rounded-full mt-1 flex-shrink-0"></div><?php endif; ?>
                    <span class="text-white font-semibold text-sm"><?= sanitize($m['name']) ?></span>
                </div>
                <span class="text-slate-500 text-xs"><?= timeAgo($m['created_at']) ?></span>
            </div>
            <div class="text-slate-400 text-xs mb-1"><?= sanitize($m['email']) ?></div>
            <div class="text-slate-500 text-xs truncate"><?= sanitize(truncate($m['message'], 60)) ?></div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Message View -->
    <div class="lg:col-span-3">
        <?php if ($viewMsg): ?>
        <div class="admin-card space-y-5">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-white font-bold text-lg"><?= sanitize($viewMsg['name']) ?></h3>
                    <div class="flex flex-wrap gap-3 mt-1 text-sm text-slate-400">
                        <a href="mailto:<?= sanitize($viewMsg['email']) ?>" class="hover:text-brand-400"><?= sanitize($viewMsg['email']) ?></a>
                        <?php if ($viewMsg['phone']): ?><span><?= sanitize($viewMsg['phone']) ?></span><?php endif; ?>
                        <?php if ($viewMsg['company']): ?><span><?= sanitize($viewMsg['company']) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="mailto:<?= sanitize($viewMsg['email']) ?>" class="admin-btn admin-btn-sm">Reply</a>
                    <a href="?delete=<?= $viewMsg['id'] ?>" onclick="return confirm('Delete?')" class="admin-btn admin-btn-danger admin-btn-sm">Delete</a>
                </div>
            </div>
            <?php if ($viewMsg['subject']): ?><div class="text-brand-400 text-sm font-semibold">Re: <?= sanitize($viewMsg['subject']) ?></div><?php endif; ?>
            <div class="p-5 bg-white/3 rounded-2xl border border-white/7 text-slate-200 text-sm leading-relaxed whitespace-pre-line"><?= sanitize($viewMsg['message']) ?></div>
            <div class="flex justify-between text-xs text-slate-500">
                <span><?= formatDate($viewMsg['created_at'], 'M j, Y g:i A') ?></span>
                <span>IP: <?= sanitize($viewMsg['ip_address'] ?? '') ?></span>
            </div>
        </div>
        <?php else: ?>
        <div class="admin-card flex items-center justify-center h-64 text-slate-500">
            <div class="text-center"><div class="text-4xl mb-3">💬</div>Select a message to read</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
