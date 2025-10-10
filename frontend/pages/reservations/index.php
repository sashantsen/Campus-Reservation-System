<?php
$title = 'My Reservations';
$active = 'reservations';
require __DIR__ . '/../../layouts/header.php';
?>
<div class="card card-pad">
  <h1 style="margin:0 0 12px 0;">Reservations</h1>

  <div class="form" style="grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
    <input class="input" id="rq" placeholder="Search">
    <select class="input" id="rstatus">
      <option value="">Status: All</option>
      <option>Booked</option>
      <option>Cancelled</option>
      <option>Pending</option>
      <option>CheckedIn</option>
    </select>
    <select class="input" id="rrange">
      <option value="month">This month</option>
      <option value="week">This week</option>
      <option value="today">Today</option>
    </select>
  </div>

  <table class="table" id="resvTable">
    <thead><tr><th>Room</th><th>Time</th><th>Status</th><th></th></tr></thead>
    <tbody><tr><td colspan="4" class="muted">Loading…</td></tr></tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const rq = document.getElementById('rq');
  const rstatus = document.getElementById('rstatus');
  const rrange = document.getElementById('rrange');
  const tb = document.querySelector('#resvTable tbody');

  async function loadReservations() {
    const params = {};
    if (rq.value.trim()) params.q = rq.value.trim();
    if (rstatus.value) params.status = rstatus.value;
    if (rrange.value) params.range = rrange.value;

    try {
      const data = await window.api('/reservations', { params });

      // ---- Robust guards to avoid .map crash ----
      if (!data) {
        tb.innerHTML = '<tr><td colspan="4" class="muted">No response from server.</td></tr>';
        return;
      }
      if (typeof data === 'string') {
        tb.innerHTML = '<tr><td colspan="4" class="muted">Unexpected HTML (likely login). Please log in and reload.</td></tr>';
        console.error('Expected JSON array, got string:', data);
        return;
      }
      if (!Array.isArray(data)) {
        const msg = data.error || data.message || 'Unexpected response.';
        tb.innerHTML = `<tr><td colspan="4" class="muted">${window.htmlescape(msg)}</td></tr>`;
        console.error('Expected array, got object:', data);
        return;
      }

      if (!data.length) {
        tb.innerHTML = '<tr><td colspan="4" class="muted">No reservations found.</td></tr>';
        return;
      }

      tb.innerHTML = data.map(r => `
        <tr>
          <td>${window.htmlescape(r.room)}</td>
          <td>${window.htmlescape(r.start)}–${window.htmlescape(r.end)}</td>
          <td><span class="badge ${r.status==='Booked'?'info':r.status==='Cancelled'?'warn':r.status==='CheckedIn'?'success':'muted'}">${window.htmlescape(r.status)}</span></td>
          <td>
            <a class="btn btn-ghost" href="<?= $BASE_URL ?>/pages/reservations/details.php?id=${r.id}">Details</a>
            ${r.status==='Booked' ? `<button class="btn btn-dark" data-id="${r.id}" data-act="checkin">Check in</button>` : ''}
          </td>
        </tr>
      `).join('');

      tb.querySelectorAll('button[data-act="checkin"]').forEach(b => {
        b.addEventListener('click', async () => {
          try {
            await window.api('/reservations/checkin', { method: 'POST', body: { id: Number(b.dataset.id) }});
            window.showToast('Checked in');
            loadReservations();
          } catch {
            window.showToast('Failed');
          }
        });
      });

    } catch (err) {
      tb.innerHTML = '<tr><td colspan="4" class="muted">Failed to load reservations.</td></tr>';
      console.error('Fetch error:', err);
    }
  }

  [rq, rstatus, rrange].forEach(el => el.addEventListener(el.tagName==='INPUT'?'input':'change', loadReservations));
  loadReservations();
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
