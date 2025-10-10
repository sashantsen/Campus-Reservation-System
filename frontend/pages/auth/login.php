<?php
$title = 'Login';
$active = '';
require __DIR__ . '/../../layouts/header.php';
?>
<div class="center" style="min-height:60vh;">
  <div class="card card-pad" style="width:min(420px,100%);">
    <h1 style="margin-top:0;">Welcome back</h1>
    <p class="muted" style="margin-top:-6px;">Sign in to continue</p>

    <form class="form" id="loginForm">
      <div>
        <label class="label">Email</label>
        <input class="input" type="email" id="email" required>
      </div>
      <div>
        <label class="label">Password</label>
        <input class="input" type="password" id="password" required>
      </div>
      <button class="btn btn-primary w-full" type="submit">Login</button>
      <div class="muted" style="font-size:14px;text-align:center;">
        Don’t have an account? <a href="<?= $BASE_URL ?>/pages/auth/register.php">Register</a>
      </div>
    </form>
  </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  const emailEl = document.getElementById('email');
  const passEl  = document.getElementById('password');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const data = await window.api('/auth/login', {
        method: 'POST',
        body: { email: emailEl.value.trim(), password: passEl.value }
      });
      // if (data && data.token) { try { localStorage.setItem('token', data.token); } catch(_) {} }
      window.showToast('Logged in!');
      location.href = "<?= $BASE_URL ?>/pages/home.php";
    } catch (err) {
      window.showToast(err.message || 'Login failed');
    }
  });
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
