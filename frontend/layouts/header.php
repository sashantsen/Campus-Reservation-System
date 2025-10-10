<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../shared/session_boot.php';

$user     = $_SESSION['user'] ?? null;
$role     = $user['role'] ?? 'student';
$title    = $title  ?? 'Campus Study Room Reservation';
$active   = $active ?? '';
$adminArea = !empty($adminArea); // set in admin pages

if (!function_exists('h')) {
  function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('initials')) {
  function initials($name) {
    $name = trim((string)$name);
    if ($name==='') return 'U';
    $p = preg_split('/\s+/', $name);
    return strtoupper(substr($p[0]??'',0,1) . substr($p[1]??'',0,1));
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($title) ?></title>

  <link rel="stylesheet" href="<?= $ASSETS_URL ?>/css/style.css?v=<?= time() ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- App config for JS -->
  <script>
    window.APP = { BASE_URL: "<?= $BASE_URL ?>", BASE_API: "<?= $BASE_API ?>" };
  </script>

  <!-- Chart.js (needed for admin dashboard charts) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

  <!-- Main app JS -->
  <script src="<?= $ASSETS_URL ?>/js/app.js?v=<?= time() ?>" defer></script>

  <!-- Optional fallback: if Chart didn't load for some reason, load it dynamically -->
  <script>
    (function ensureChart(){
      if (window.Chart) return;
      var s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js';
      s.onload = function(){ console.info('Chart.js loaded (fallback)'); };
      document.head.appendChild(s);
    })();
  </script>
</head>

<body class="app-body<?= $adminArea ? ' admin-area' : '' ?>">

<header class="app-header">
  <div class="container nav-wrap">
    <!-- Brand goes to Admin Dashboard inside admin area -->
    <a class="brand" href="<?= $adminArea ? ($BASE_URL.'/pages/admin/dashboard.php') : ($BASE_URL.'/pages/home.php') ?>">
      Campus Rooms
    </a>

    <!-- Desktop nav -->
    <nav class="nav" id="desktopNav">
      <?php if ($adminArea && $role === 'admin'): ?>
        <!-- ADMIN NAV ONLY -->
        <a class="nav-link <?= $active==='admin'&&strpos($title,'Dashboard')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/dashboard.php">Dashboard</a>
        <a class="nav-link <?= strpos($title,'Users')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/users.php">Users</a>
        <a class="nav-link <?= strpos($title,'Rooms')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/rooms.php">Rooms</a>
        <a class="nav-link <?= strpos($title,'Reservations')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/reservations.php">Reservations</a>
      <?php else: ?>
        <!-- STANDARD SITE NAV -->
        <a class="nav-link <?= $active==='home'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/home.php">Home</a>
        <a class="nav-link <?= $active==='rooms'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/rooms/index.php">Rooms</a>
        <?php if ($role === 'student'): ?>
          <a class="nav-link <?= $active==='reservations'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/reservations/index.php">Reservations</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
          <a class="nav-link <?= $active==='admin'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/dashboard.php">Admin</a>
        <?php endif; ?>
        <a class="nav-link <?= $active==='about'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/about.php">About</a>
        <a class="nav-link <?= $active==='support'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/support.php">Support</a>
      <?php endif; ?>
    </nav>

    <div class="nav-right">
      <?php if ($user): ?>
        <div class="profile" id="profileRoot">
          <button class="profile-btn" id="profileBtn" aria-expanded="false" aria-haspopup="true" type="button" title="<?= h($user['name'] ?? 'User') ?>">
            <span class="avatar">
              <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?= h($user['avatar_url']) ?>" alt="<?= h($user['name'] ?? 'User') ?>">
              <?php else: ?>
                <?= h(initials($user['name'] ?? ($user['email'] ?? 'U'))) ?>
              <?php endif; ?>
            </span>
            <span class="profile-meta">
              <div class="profile-name"><?= h($user['name'] ?? 'User') ?></div>
              <div class="profile-email"><?= h($user['email'] ?? '') ?></div>
            </span>
          </button>

          <div class="profile-menu" id="profileMenu" hidden role="menu">
            <div class="head">
              <div class="name"><?= h($user['name'] ?? 'User') ?></div>
              <div class="email"><?= h($user['email'] ?? '') ?></div>
              <div class="badges">
                <span class="badge"><?= h(strtoupper($role)) ?></span>
                <?php if (!empty($user['student_id'])): ?>
                  <span class="badge">ID: <?= h($user['student_id']) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- In admin area, keep menu minimal -->
            <?php if ($adminArea && $role==='admin'): ?>
              <a class="item" role="menuitem" href="<?= $BASE_URL ?>/pages/admin/dashboard.php">Admin Dashboard</a>
              <a class="item" role="menuitem" href="<?= $BASE_URL ?>/pages/profile.php">Profile</a>
              <a class="item danger" role="menuitem" href="<?= $BASE_URL ?>/pages/auth/logout.php">Logout</a>
            <?php else: ?>
              <a class="item" role="menuitem" href="<?= $BASE_URL ?>/pages/profile.php">Profile</a>
              <a class="item" role="menuitem" href="<?= $BASE_URL ?>/pages/profile.php?edit=1">Edit Profile</a>
              <a class="item" role="menuitem" href="<?= $BASE_URL ?>/pages/reservations/index.php">My Reservations</a>
              <a class="item danger" role="menuitem" href="<?= $BASE_URL ?>/pages/auth/logout.php">Logout</a>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <a class="btn btn-ghost" href="<?= $BASE_URL ?>/pages/auth/login.php">Login</a>
        <a class="btn btn-primary" href="<?= $BASE_URL ?>/pages/auth/register.php">Register</a>
      <?php endif; ?>

      <!-- Hamburger (mobile) -->
      <button class="hamburger" id="hamburger" aria-label="Toggle menu" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <!-- Mobile nav -->
  <div class="mobile-nav" id="mobileNav" hidden>
    <?php if ($adminArea && $role==='admin'): ?>
      <a class="<?= strpos($title,'Dashboard')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/dashboard.php">Dashboard</a>
      <a class="<?= strpos($title,'Users')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/users.php">Users</a>
      <a class="<?= strpos($title,'Rooms')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/rooms.php">Rooms</a>
      <a class="<?= strpos($title,'Reservations')!==false?'active':'' ?>" href="<?= $BASE_URL ?>/pages/admin/reservations.php">Reservations</a>
      <div class="mobile-actions">
        <a class="btn btn-ghost" href="<?= $BASE_URL ?>/pages/profile.php">Profile</a>
        <a class="btn btn-dark"  href="<?= $BASE_URL ?>/pages/auth/logout.php">Logout</a>
      </div>
    <?php else: ?>
      <a class="<?= $active==='home'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/home.php">Home</a>
      <a class="<?= $active==='rooms'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/rooms/index.php">Rooms</a>
      <?php if ($role==='student'): ?>
        <a class="<?= $active==='reservations'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/reservations/index.php">Reservations</a>
      <?php endif; ?>
      <a class="<?= $active==='about'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/about.php">About</a>
      <a class="<?= $active==='support'?'active':'' ?>" href="<?= $BASE_URL ?>/pages/support.php">Support</a>
      <div class="mobile-actions">
        <?php if ($user): ?>
          <a class="btn btn-ghost" href="<?= $BASE_URL ?>/pages/profile.php">Profile</a>
          <a class="btn btn-dark"  href="<?= $BASE_URL ?>/pages/auth/logout.php">Logout</a>
        <?php else: ?>
          <a class="btn btn-ghost" href="<?= $BASE_URL ?>/pages/auth/login.php">Login</a>
          <a class="btn btn-primary" href="<?= $BASE_URL ?>/pages/auth/register.php">Register</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</header>

<main class="app-main container">
  <div id="toast" class="toast" hidden></div>
