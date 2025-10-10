<?php
$title = 'Register';
$active = '';
require __DIR__ . '/../../layouts/header.php';
?>
<div class="center" style="min-height:60vh;">
  <div class="card card-pad" style="width:min(480px,100%);">
    <h1 style="margin-top:0;">Create account</h1>
    <form class="form" id="regForm">
      <div><label class="label">Full name</label><input class="input" id="name" required></div>
      <div><label class="label">Student ID</label><input class="input" id="student_id" placeholder="e.g. 2408414" required></div>
      <div><label class="label">Email</label><input class="input" type="email" id="remail" required></div>
      <div><label class="label">Password</label><input class="input" type="password" id="rpass" minlength="6" required></div>
      <button class="btn btn-primary w-full" type="submit">Register</button>
      <div class="muted" style="font-size:14px;text-align:center;margin-top:8px;">
        Already have an account? <a href="<?= $BASE_URL ?>/pages/auth/login.php">Login</a>
      </div>
    </form>
  </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
  const form   = document.getElementById('regForm');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      await window.api('/auth/register', {
        method: 'POST',
        body: {
          name: document.getElementById('name').value.trim(),
          student_id: document.getElementById('student_id').value.trim(),
          email: document.getElementById('remail').value.trim(),
          password: document.getElementById('rpass').value
        }
      });
      window.showToast('Account created');
      setTimeout(() => { location.href = "<?= $BASE_URL ?>/pages/home.php"; }, 700);
    } catch (err) {
      window.showToast(err.message || 'Registration failed');
    }
  });
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
