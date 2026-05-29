<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        try {
            $user = fetchOne("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin']      = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
                // Update last login
                query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                header('Location: ' . SITE_URL . '/admin/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
                sleep(1);
            }
        } catch (Exception $e) {
            $error = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — Tedmark CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:#0b1528;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
.login-wrap{width:100%;max-width:420px;}
.login-logo{text-align:center;margin-bottom:32px;}
.login-logo img{height:70px;width:auto;}
.login-logo p{font-size:0.8rem;color:#64748b;margin-top:8px;letter-spacing:0.04em;text-transform:uppercase;}
.login-card{background:#1e293b;border:1px solid #1e293b;border-radius:16px;padding:36px 32px;box-shadow:0 24px 64px rgba(0,0,0,0.4);}
.login-card h1{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.3rem;font-weight:800;color:#fff;margin-bottom:6px;}
.login-card .sub{font-size:0.85rem;color:#64748b;margin-bottom:28px;}
label{display:block;font-size:0.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;}
.input-wrap{position:relative;margin-bottom:18px;}
.input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#475569;font-size:0.85rem;}
input[type=email],input[type=password]{width:100%;background:#0b1528;border:1px solid #334155;border-radius:8px;padding:11px 14px 11px 38px;color:#fff;font-size:0.9rem;font-family:'Inter',sans-serif;outline:none;transition:border .15s;}
input:focus{border-color:#22c55e;}
.btn-login{width:100%;padding:12px;background:#22c55e;color:#000;font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:0.95rem;border:none;border-radius:8px;cursor:pointer;transition:background .15s;margin-top:4px;}
.btn-login:hover{background:#16a34a;}
.alert-error{background:rgba(244,63,94,0.12);border:1px solid rgba(244,63,94,0.3);color:#fda4af;padding:12px 16px;border-radius:8px;font-size:0.85rem;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.back-link{text-align:center;margin-top:20px;}
.back-link a{color:#475569;font-size:0.82rem;text-decoration:none;}
.back-link a:hover{color:#94a3b8;}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-logo">
    <img src="<?= SITE_URL ?>/assets/images/tedmark logo copy2.png" alt="Tedmark Digital Agency">
    <p>Admin Panel</p>
  </div>

  <div class="login-card">
    <h1>Welcome back</h1>
    <p class="sub">Sign in to manage your website</p>

    <?php if ($error): ?>
    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div>
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <i class="fa-solid fa-envelope"></i>
          <input type="email" id="email" name="email" required autofocus placeholder="admin@tedmarkdigital.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
      </div>
      <div>
        <label for="password">Password</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
      </div>
      <button type="submit" class="btn-login">Sign In <i class="fa-solid fa-arrow-right" style="margin-left:6px;"></i></button>
    </form>
  </div>

  <div class="back-link">
    <a href="<?= SITE_URL ?>/"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i>Back to website</a>
  </div>
</div>
</body>
</html>
