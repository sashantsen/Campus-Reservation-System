<?php
$title  = 'Profile';
$active = 'profile';
require __DIR__ . '/../layouts/header.php';

if (empty($_SESSION['user'])) {
  header("Location: {$BASE_URL}/pages/auth/login.php");
  exit;
}
$user   = $_SESSION['user'];
$isEdit = isset($_GET['edit']) && $_GET['edit'] === '1';

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
<div class="card card-pad max-w-md" style="margin:0 auto;">
  <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <h1>My Profile</h1>
    <?php if ($isEdit): ?>
      <a href="<?= $BASE_URL ?>/pages/profile.php" class="btn btn-ghost">Cancel</a>
    <?php else: ?>
      <a href="<?= $BASE_URL ?>/pages/profile.php?edit=1" class="btn btn-primary">Edit Profile</a>
    <?php endif; ?>
  </div>

  <?php if ($isEdit): ?>
    <form class="form" id="editForm" style="margin-top:16px; grid-template-columns:1fr 1fr; gap:16px;">
      <div style="grid-column:1 / -1; display:flex; align-items:center; gap:14px;">
        <div class="avatar" style="width:56px; height:56px;">
          <?php if (!empty($user['avatar_url'])): ?>
            <img src="<?= h($user['avatar_url']) ?>" alt="<?= h($user['name']) ?>">
          <?php else: ?>
            <?= h(initials($user['name'] ?? 'U')) ?>
          <?php endif; ?>
        </div>
        <div class="help">Paste an image URL below to change your avatar.</div>
      </div>

      <div style="grid-column:1 / -1;">
        <label class="label">Avatar URL</label>
        <input class="input" name="avatar_url" value="<?= h($user['avatar_url'] ?? '') ?>" placeholder="https://...">
      </div>

      <div>
        <label class="label">Full Name</label>
        <input class="input" name="name" value="<?= h($user['name']) ?>" required>
      </div>
      <div>
        <label class="label">Email</label>
        <input class="input" value="<?= h($user['email']) ?>" disabled>
      </div>
      <div>
        <label class="label">Student ID</label>
        <input class="input" value="<?= h($user['student_id']) ?>" disabled>
      </div>
      <div>
        <label class="label">Role</label>
        <input class="input" value="<?= strtoupper(h($user['role'] ?? 'student')) ?>" disabled>
      </div>

      <div style="grid-column:1 / -1; border-top:1px solid var(--border); padding-top:12px;">
        <div class="section-title">Change Password (optional)</div>
      </div>
      <div>
        <label class="label">Current Password</label>
        <input class="input" type="password" name="current_password">
      </div>
      <div>
        <label class="label">New Password</label>
        <input class="input" type="password" name="new_password">
      </div>

      <div style="grid-column:1 / -1; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">Save changes</button>
        <a href="<?= $BASE_URL ?>/pages/profile.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>

    <script>
    // NOTE: your window.api signature is api(path, {method, body})
    document.getElementById('editForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const body = Object.fromEntries(fd.entries());

      const tasks = [];
      // 1) Profile update (name + avatar_url). Email is not changed here.
      tasks.push(window.api('/auth/update-profile', {
        method: 'POST',
        body: {
          name: body.name,
          avatar_url: body.avatar_url ?? ''
        }
      }));

      // 2) Optional password change
      if ((body.current_password || '').trim() !== '' || (body.new_password || '').trim() !== '') {
        tasks.push(window.api('/auth/change-password', {
          method: 'POST',
          body: {
            current_password: body.current_password || '',
            new_password: body.new_password || ''
          }
        }));
      }

      try{
        await Promise.all(tasks);
        window.showToast && window.showToast('Profile saved');
        location.href = '<?= $BASE_URL ?>/pages/profile.php';
      }catch(err){
        alert('Update failed: ' + err.message);
      }
    });
    </script>

  <?php else: ?>
    <div class="form" style="grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;">
      <div style="grid-column:1 / -1; display:flex; gap:14px; align-items:center;">
        <div class="avatar" style="width:56px; height:56px;">
          <?php if (!empty($user['avatar_url'])): ?>
            <img src="<?= h($user['avatar_url']) ?>" alt="<?= h($user['name']) ?>">
          <?php else: ?>
            <?= h(initials($user['name'] ?? 'U')) ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="label"><?= h($user['name']) ?></div>
          <div class="muted"><?= h($user['email']) ?></div>
        </div>
      </div>

      <div>
        <label class="label">Full Name</label>
        <input class="input" value="<?= h($user['name']) ?>" disabled>
      </div>
      <div>
        <label class="label">Email Address</label>
        <input class="input" value="<?= h($user['email']) ?>" disabled>
      </div>
      <div>
        <label class="label">Student ID</label>
        <input class="input" value="<?= h($user['student_id']) ?>" disabled>
      </div>
      <div>
        <label class="label">Role</label>
        <input class="input" value="<?= strtoupper(h($user['role'] ?? 'student')) ?>" disabled>
      </div>
    </div>

    <div style="margin-top:24px; display:flex; gap:10px; flex-wrap:wrap;">
      <a href="<?= $BASE_URL ?>/pages/reservations/index.php" class="btn">My Reservations</a>
      <a href="<?= $BASE_URL ?>/pages/profile.php?edit=1" class="btn btn-primary">Edit Profile</a>
      <a href="<?= $BASE_URL ?>/pages/auth/logout.php" class="btn btn-dark">Logout</a>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
