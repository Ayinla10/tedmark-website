<?php
$pageTitle = 'Admin Users';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$editing = null;
$success = $error = '';
$me = $_SESSION['admin_id'] ?? 0;

// Delete user (cannot delete self)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId === (int)$me) {
        $error = 'You cannot delete your own account.';
    } else {
        query("DELETE FROM users WHERE id = ?", [$delId]);
        header('Location: ' . SITE_URL . '/admin/users.php'); exit;
    }
}

// Edit load
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editing = fetchOne("SELECT id, name, email, role FROM users WHERE id = ?", [$_GET['edit']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $role   = $_POST['role'] ?? 'admin';
    $pass   = $_POST['password'] ?? '';
    $editId = (int)($_POST['edit_id'] ?? 0);

    if (!$name || !$email) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        if ($editId) {
            // Check email not taken by another user
            $taken = fetchOne("SELECT id FROM users WHERE email=? AND id!=?", [$email, $editId]);
            if ($taken) { $error = 'That email is already in use.'; }
            else {
                if ($pass) {
                    query("UPDATE users SET name=?,email=?,role=?,password=? WHERE id=?",
                        [$name,$email,$role,password_hash($pass,PASSWORD_BCRYPT),$editId]);
                } else {
                    query("UPDATE users SET name=?,email=?,role=? WHERE id=?",
                        [$name,$email,$role,$editId]);
                }
                $success = 'User updated!';
                $editing = null;
            }
        } else {
            if (!$pass) { $error = 'Password is required for new users.'; }
            else {
                $taken = fetchOne("SELECT id FROM users WHERE email=?", [$email]);
                if ($taken) { $error = 'That email is already registered.'; }
                else {
                    query("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)",
                        [$name,$email,password_hash($pass,PASSWORD_BCRYPT),$role]);
                    $success = 'Admin user created!';
                }
            }
        }
    }
}

$users = fetchAll("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$e = $editing ?? [];

require_once __DIR__ . '/includes/admin-layout.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-check"></i> <?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

  <div class="tm-card">
    <div class="tm-card-header">
      <span class="tm-card-title">Admin Users <span class="badge badge-gray" style="margin-left:8px;"><?= count($users) ?></span></span>
    </div>
    <table class="tm-table">
      <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:50%;background:<?= $u['id']==$me?'rgba(34,197,94,0.15)':'#1e293b' ?>;display:flex;align-items:center;justify-content:center;color:#22c55e;font-weight:700;border:<?= $u['id']==$me?'1px solid #22c55e':'1px solid #1e293b' ?>;">
              <?= strtoupper(substr($u['name'],0,1)) ?>
            </div>
            <div>
              <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($u['name']) ?><?php if($u['id']==$me): ?> <span class="badge badge-green" style="font-size:0.65rem;margin-left:4px;">You</span><?php endif; ?></div>
            </div>
          </div>
        </td>
        <td style="color:#94a3b8;"><?= htmlspecialchars($u['email']) ?></td>
        <td><span class="badge <?= $u['role']==='superadmin'?'badge-amber':'badge-gray' ?>"><?= ucfirst($u['role']) ?></span></td>
        <td style="color:#64748b;font-size:0.8rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
        <td>
          <div class="gap-8">
            <a href="?edit=<?= $u['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
            <?php if ($u['id'] != $me): ?>
            <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete <?= htmlspecialchars(addslashes($u['name'])) ?>? This cannot be undone.')"><i class="fa-solid fa-trash"></i></a>
            <?php else: ?>
            <span class="btn btn-ghost btn-sm" style="opacity:.3;cursor:not-allowed;" title="Cannot delete yourself"><i class="fa-solid fa-trash"></i></span>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="tm-card" style="position:sticky;top:80px;">
    <div class="tm-card-header"><span class="tm-card-title"><?= $editing ? 'Edit User' : 'Add Admin User' ?></span></div>
    <form method="POST">
      <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="name" value="<?= htmlspecialchars($e['name']??'') ?>" required>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="text" name="email" value="<?= htmlspecialchars($e['email']??'') ?>" required autocomplete="off">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="admin" <?= ($e['role']??'admin')==='admin'?'selected':'' ?>>Admin</option>
          <option value="superadmin" <?= ($e['role']??'')==='superadmin'?'selected':'' ?>>Super Admin</option>
          <option value="editor" <?= ($e['role']??'')==='editor'?'selected':'' ?>>Editor</option>
        </select>
      </div>
      <div class="form-group">
        <label><?= $editing ? 'New Password' : 'Password *' ?></label>
        <div style="position:relative;">
          <input type="password" name="password" id="pwdInput" autocomplete="new-password" placeholder="<?= $editing?'Leave blank to keep current':'' ?>">
          <button type="button" onclick="togglePwd()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#64748b;cursor:pointer;font-size:0.85rem;"><i class="fa-solid fa-eye" id="eyeIcon"></i></button>
        </div>
        <?php if ($editing): ?><small style="color:#64748b;font-size:0.75rem;">Leave blank to keep the current password.</small><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> <?= $editing?'Update User':'Create User' ?></button>
      <?php if ($editing): ?><a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:8px;">Cancel</a><?php endif; ?>
    </form>

    <!-- Security note -->
    <div style="margin-top:20px;padding:14px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:8px;">
      <p style="color:#f59e0b;font-size:0.78rem;font-weight:600;margin-bottom:4px;"><i class="fa-solid fa-triangle-exclamation"></i> Security Tips</p>
      <ul style="color:#94a3b8;font-size:0.75rem;padding-left:16px;line-height:1.8;">
        <li>Use strong, unique passwords (16+ chars)</li>
        <li>Only create accounts for trusted staff</li>
        <li>Remove inactive accounts promptly</li>
      </ul>
    </div>
  </div>
</div>

<script>
function togglePwd() {
  const i = document.getElementById('pwdInput');
  const e = document.getElementById('eyeIcon');
  if (i.type === 'password') { i.type='text'; e.className='fa-solid fa-eye-slash'; }
  else { i.type='password'; e.className='fa-solid fa-eye'; }
}
</script>

<?php require_once __DIR__ . '/includes/admin-layout-end.php'; ?>
