<?php
$title = 'Reservation Details';
$active = 'reservations';
require __DIR__ . '/../../layouts/header.php';
$id = (int)($_GET['id'] ?? 0);
?>
<div class="grid-2" id="detailWrap">
  <div class="card card-pad">
    <h1 style="margin-top:0;" id="title">Loading…</h1>
    <p class="muted" id="by"></p>
    <div class="stack" id="info"></div>
  </div>
  <div class="card card-pad">
    <h2 style="margin-top:0;">Actions</h2>
    <button class="btn btn-dark w-full" id="checkinBtn">Check in</button>
    <button class="btn btn-ghost w-full" style="margin-top:8px;" id="cancelBtn">Cancel</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const id = <?= $id ?: 0 ?>;
  const wrap = document.getElementById('detailWrap');

  const fail = () => wrap.innerHTML = '<div class="card card-pad">Failed to load reservation.</div>';
  if (!id) return fail();

  try {
    const r = await window.api('/reservations/get', { params: { id }});
    document.getElementById('title').textContent = `${window.htmlescape(r.room)} — ${window.htmlescape(r.start)}–${window.htmlescape(r.end)}`;
    document.getElementById('by').textContent = `Booked by ${window.htmlescape(r.user || 'You')}`;
    document.getElementById('info').innerHTML = `
      <div><strong>Status:</strong> <span class="badge ${r.status==='Booked'?'info':(r.status==='Cancelled'?'warn':r.status==='CheckedIn'?'success':'muted')}">${window.htmlescape(r.status)}</span></div>
      ${r.notes ? `<div><strong>Notes:</strong> ${window.htmlescape(r.notes)}</div>` : ''}
    `;

    document.getElementById('checkinBtn').onclick = async () => {
      try {
        await window.api('/reservations/checkin', { method:'POST', body:{ id } });
        window.showToast('Checked in');
        location.reload();
      } catch { window.showToast('Failed'); }
    };

    document.getElementById('cancelBtn').onclick = async () => {
      try {
        await window.api('/reservations/cancel', { method:'POST', body:{ id } });
        window.showToast('Cancelled');
        location.href = "<?= $BASE_URL ?>/pages/reservations/index.php";
      } catch { window.showToast('Failed'); }
    };
  } catch (err) {
    console.error(err);
    fail();
  }
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
