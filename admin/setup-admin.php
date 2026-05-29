<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        query("DELETE FROM users");
        query("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')", [$name, $email, $hash]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Setup — Tedmark</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:sans-serif;background:#0b1528;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
.card{background:#1e293b;border-radius:12px;padding:36px 32px;width:100%;max-width:400px;}
h1{color:#fff;font-size:1.2rem;font-weight:700;margin-bottom:6px;}
p{color:#64748b;font-size:0.85rem;margin-bottom:24px;}
label{display:block;color:#94a3b8;font-size:0.78rem;font-weight:600;text-transform:uppercase;margin-bottom:6px;margin-top:16px;}
input{width:100%;background:#0b1528;border:1px solid #334155;border-radius:8px;padding:10px 14px;color:#fff;font-size:0.9rem;outline:none;}
input:focus{border-color:#22c55e;}
button{width:100%;margin-top:20px;padding:12px;background:#22c55e;color:#000;font-weight:700;border:none;border-radius:8px;cursor:pointer;font-size:0.95rem;}
.error{background:rgba(244,63,94,0.15);color:#fda4af;padding:10px 14px;border-radius:8px;font-size:0.85rem;margin-bottom:16px;}
.success{background:rgba(34,197,94,0.15);color:#86efac;padding:16px;border-radius:8px;font-size:0.9rem;text-align:center;}
.success a{color:#22c55e;font-weight:700;}
</style>
</head>
<body>
<div class="card">
  <h1>Create Admin Account</h1>
  <p>This will replace any existing admin accounts.</p>

  <?php if ($done): ?>
  <div class="success">
    ✅ Admin account created!<br><br>
    <a href="<?= SITE_URL ?>/admin/login.php">Go to Login →</a><br><br>
    <small style="color:#64748b;">Delete this file after logging in!</small>
  </div>
  <?php else: ?>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label>Your Name</label>
    <input type="text" name="name" required placeholder="Tedmark Admin" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    <label>Email Address</label>
    <input type="email" name="email" required placeholder="admin@tedmarkdigital.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    <label>Password</label>
    <input type="password" name="password" required placeholder="Choose a strong password">
    <button type="submit">Create Admin Account</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
