<?php $title='Support'; $active='support'; require __DIR__.'/../layouts/header.php'; ?>
<div class="grid-2">
  <div class="card card-pad">
    <h1>Support</h1>
    <p class="muted">Need help? Send us a message.</p>
    <form class="form" onsubmit="event.preventDefault(); showToast('Message sent!'); this.reset();" >
      <div><label class="label">Your email</label><input class="input" type="email" required></div>
      <div><label class="label">Message</label><textarea class="input" rows="4" required></textarea></div>
      <button class="btn btn-primary" type="submit">Send</button>
    </form>
  </div>
  <div class="card card-pad">
    <h2>FAQ</h2>
    <p><strong>How do I book?</strong><br><span class="muted">Go to Rooms → Reserve.</span></p>
  </div>
</div>
<script type="module">import {showToast} from "<?= $BASE_URL ?>/assets/js/app.js";</script>
<?php require __DIR__.'/../layouts/footer.php'; ?>
