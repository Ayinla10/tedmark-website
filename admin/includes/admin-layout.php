<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$admin = currentAdmin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$nav = [
    'dashboard'    => ['icon'=>'fa-gauge',          'label'=>'Dashboard',    'href'=>'dashboard.php'],
    'homepage'     => ['icon'=>'fa-house',           'label'=>'Homepage',     'href'=>'homepage.php',     'group'=>'Content'],
    'about'        => ['icon'=>'fa-circle-info',     'label'=>'About',        'href'=>'about.php'],
    'services'     => ['icon'=>'fa-briefcase',       'label'=>'Services',     'href'=>'services.php'],
    'industries'   => ['icon'=>'fa-building',        'label'=>'Industries',   'href'=>'industries.php'],
    'posts'        => ['icon'=>'fa-newspaper',       'label'=>'Blog Posts',   'href'=>'posts.php',        'group'=>'Blog'],
    'post-edit'    => ['icon'=>'fa-plus',            'label'=>'New Post',     'href'=>'post-edit.php'],
    'projects'     => ['icon'=>'fa-folder-open',     'label'=>'Projects',     'href'=>'projects.php',     'group'=>'Portfolio'],
    'project-edit' => ['icon'=>'fa-plus',            'label'=>'New Project',  'href'=>'project-edit.php'],
    'team'         => ['icon'=>'fa-users',           'label'=>'Team',         'href'=>'team.php',         'group'=>'People'],
    'testimonials' => ['icon'=>'fa-star',            'label'=>'Testimonials', 'href'=>'testimonials.php'],
    'messages'     => ['icon'=>'fa-envelope',        'label'=>'Messages',     'href'=>'messages.php',     'group'=>'Inbox'],
    'settings'     => ['icon'=>'fa-gear',            'label'=>'Settings',     'href'=>'settings.php',     'group'=>'Settings'],
    'seo'          => ['icon'=>'fa-magnifying-glass-chart','label'=>'SEO Manager',  'href'=>'seo.php'],
    'users'        => ['icon'=>'fa-user-shield',     'label'=>'Admin Users',  'href'=>'users.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — Tedmark CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --bg:#0f172a;--sidebar:#0b1528;--sidebar-w:240px;
  --accent:#22c55e;--accent2:#f59e0b;
  --text:#e2e8f0;--muted:#64748b;--border:#1e293b;
  --card:#1e293b;--input:#0b1528;--radius:10px;--topbar:60px;
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:0.9rem;}
/* SIDEBAR */
.tm-sidebar{width:var(--sidebar-w);min-width:var(--sidebar-w);background:var(--sidebar);height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;border-right:1px solid var(--border);z-index:100;overflow-y:auto;}
.tm-sidebar-logo{padding:20px 20px 16px;border-bottom:1px solid var(--border);}
.tm-sidebar-logo img{height:52px;width:auto;}
.tm-sidebar-logo span{display:block;font-size:0.65rem;color:var(--muted);margin-top:4px;letter-spacing:0.05em;text-transform:uppercase;}
.tm-sidebar nav{flex:1;padding:12px 0;}
.tm-nav-group{font-size:0.65rem;font-weight:700;color:var(--muted);letter-spacing:0.08em;text-transform:uppercase;padding:16px 20px 6px;}
.tm-nav-item{display:flex;align-items:center;gap:10px;padding:9px 20px;color:#94a3b8;text-decoration:none;font-size:0.85rem;font-weight:500;transition:all .15s;border-left:3px solid transparent;}
.tm-nav-item:hover{color:#fff;background:rgba(255,255,255,0.04);}
.tm-nav-item.active{color:var(--accent);background:rgba(34,197,94,0.08);border-left-color:var(--accent);}
.tm-nav-item i{width:16px;text-align:center;font-size:0.85rem;}
.tm-sidebar-footer{padding:16px 20px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:10px;}
.tm-sidebar-footer a{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:0.8rem;text-decoration:none;}
.tm-sidebar-footer a:hover{color:#fff;}
.tm-sidebar-footer a.logout:hover{color:#f43f5e;}
/* MAIN */
.tm-main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;}
/* TOPBAR */
.tm-topbar{height:var(--topbar);background:var(--sidebar);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 28px;position:sticky;top:0;z-index:50;}
.tm-topbar-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:700;color:#fff;}
.tm-topbar-right{display:flex;align-items:center;gap:16px;}
.tm-admin-badge{background:var(--accent);color:#000;font-size:0.7rem;font-weight:700;padding:3px 9px;border-radius:20px;}
/* CONTENT */
.tm-content{padding:28px;flex:1;}
/* CARDS */
.tm-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;}
.tm-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.tm-card-title{font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:1rem;color:#fff;}
/* FORMS */
label{display:block;font-size:0.78rem;font-weight:600;color:#94a3b8;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.04em;}
input[type=text],input[type=email],input[type=password],input[type=url],input[type=number],select,textarea{width:100%;background:var(--input);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:#fff;font-size:0.9rem;font-family:'Inter',sans-serif;outline:none;transition:border .15s;}
input:focus,select:focus,textarea:focus{border-color:var(--accent);}
textarea{resize:vertical;min-height:140px;}
select option{background:var(--input);}
/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .15s;font-family:'Inter',sans-serif;}
.btn-primary{background:var(--accent);color:#000;}
.btn-primary:hover{background:#16a34a;color:#000;}
.btn-danger{background:#f43f5e;color:#fff;}
.btn-danger:hover{background:#e11d48;}
.btn-ghost{background:transparent;color:#94a3b8;border:1px solid var(--border);}
.btn-ghost:hover{color:#fff;border-color:#475569;}
.btn-amber{background:var(--accent2);color:#000;}
.btn-amber:hover{background:#d97706;color:#000;}
.btn-sm{padding:6px 12px;font-size:0.78rem;}
/* TABLE */
.tm-table{width:100%;border-collapse:collapse;}
.tm-table th{text-align:left;font-size:0.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;padding:10px 14px;border-bottom:1px solid var(--border);}
.tm-table td{padding:12px 14px;border-bottom:1px solid rgba(30,41,59,0.6);font-size:0.85rem;vertical-align:middle;}
.tm-table tr:last-child td{border-bottom:none;}
.tm-table tr:hover td{background:rgba(255,255,255,0.02);}
/* BADGES */
.badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:0.7rem;font-weight:700;}
.badge-green{background:rgba(34,197,94,0.15);color:#22c55e;}
.badge-amber{background:rgba(245,158,11,0.15);color:#f59e0b;}
.badge-red{background:rgba(244,63,94,0.15);color:#f43f5e;}
.badge-blue{background:rgba(96,165,250,0.15);color:#60a5fa;}
.badge-gray{background:rgba(100,116,139,0.15);color:#94a3b8;}
/* ALERTS */
.alert{padding:12px 16px;border-radius:8px;font-size:0.85rem;margin-bottom:20px;}
.alert-success{background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.3);color:#86efac;}
.alert-error{background:rgba(244,63,94,0.12);border:1px solid rgba(244,63,94,0.3);color:#fda4af;}
/* GRID */
.tm-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
.tm-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;}
.tm-grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;}
/* STAT CARDS */
.tm-stat{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;display:flex;align-items:center;gap:16px;}
.tm-stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.tm-stat-value{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.6rem;font-weight:800;color:#fff;line-height:1;}
.tm-stat-label{font-size:0.78rem;color:var(--muted);margin-top:4px;}
/* MISC */
.tm-divider{border:none;border-top:1px solid var(--border);margin:24px 0;}
.text-muted{color:var(--muted);}
.text-green{color:var(--accent);}
.text-amber{color:var(--accent2);}
.gap-8{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.form-group{margin-bottom:18px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:#334155;border-radius:10px;}
</style>
</head>
<body>

<aside class="tm-sidebar">
  <div class="tm-sidebar-logo">
    <img src="<?= SITE_URL ?>/assets/images/tedmark logo copy2.png" alt="Tedmark">
    <span>Content Management</span>
  </div>
  <nav>
    <?php
    $lastGroup = null;
    foreach($nav as $key => $item):
      $group = $item['group'] ?? null;
      if($group && $group !== $lastGroup){ $lastGroup = $group;
        echo '<div class="tm-nav-group">'.htmlspecialchars($group).'</div>';
      }
      $active = $currentPage === $key ? ' active' : '';
    ?>
    <a class="tm-nav-item<?= $active ?>" href="<?= SITE_URL ?>/admin/<?= $item['href'] ?>">
      <i class="fa-solid <?= $item['icon'] ?>"></i><?= $item['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="tm-sidebar-footer">
    <a href="<?= SITE_URL ?>/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Website</a>
    <a class="logout" href="<?= SITE_URL ?>/admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>
</aside>

<div class="tm-main">
  <div class="tm-topbar">
    <span class="tm-topbar-title"><?= htmlspecialchars($pageTitle ?? 'Admin') ?></span>
    <div class="tm-topbar-right">
      <span class="tm-admin-badge">ADMIN</span>
      <span style="color:#94a3b8;font-size:0.82rem;"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></span>
    </div>
  </div>
  <div class="tm-content">
