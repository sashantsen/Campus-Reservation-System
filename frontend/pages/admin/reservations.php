<?php
$adminArea = true;  
$title='Admin • Reservations'; $active='admin';
require __DIR__.'/../../layouts/header.php';
if (($role ?? 'student') !== 'admin') { header("Location: {$BASE_URL}/pages/home.php"); exit; }
?>
<div class="card card-pad">
  <h1 style="margin:0 0 12px 0;">All Reservations</h1>

  <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
    <input class="input" id="q" placeholder="Filter by room name" style="width:240px">
    <select class="input" id="status">
      <option value="">Any status</option>
      <option>Pending</option><option>Approved</option><option>Cancelled</option><option>Booked</option><option>CheckedIn</option>
    </select>
    <select class="input" id="range">
      <option value="">All time</option><option value="today">Today</option><option value="week">This week</option><option value="month">This month</option>
    </select>
    <button class="btn" id="applyFilters">Apply</button>
  </div>

  <table class="table" id="admResv">
    <thead><tr><th>User</th><th>Room</th><th>Start</th><th>End</th><th>Status</th><th></th></tr></thead>
    <tbody><tr><td colspan="6" class="muted">Loading…</td></tr></tbody>
  </table>
</div>

<!-- Status Modal -->
<div class="modal-backdrop" id="resvModal" hidden>
  <div class="modal">
    <div class="hd" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
      <h2 id="resvTitle" style="margin:0;font-size:18px;">Update Status</h2>
      <button class="btn btn-ghost" onclick="closeResvModal()">✕</button>
    </div>
    <div class="bd" style="padding:20px;">
      <form id="resvForm" class="form">
        <input type="hidden" id="resvId">
        <div>
          <label class="label">Status</label>
          <select class="input" id="resvStatus" required>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;">
          <button type="button" class="btn btn-ghost" onclick="closeResvModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.modal-backdrop{ position:fixed; inset:0; background:rgba(15,23,42,.45); display:flex; align-items:center; justify-content:center; z-index:80; }
.modal{ background:#fff; border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow); width:460px; max-width:95vw; animation:fadeIn .15s ease-out; }
@keyframes fadeIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
</style>

<script>
let currentRow = null;

async function loadRes(){
  const params = { scope:'all', q: q.value.trim(), status: status.value, range: range.value };
  const rows = await window.api('/reservations', { params });
  const tb = document.querySelector('#admResv tbody');
  tb.innerHTML = rows.map(r => `
    <tr data-id="${r.id}">
      <td>${window.htmlescape(r.user_name || r.user || '')}</td>
      <td>${window.htmlescape(r.room || r.room_name || '')}</td>
      <td>${window.htmlescape(r.start || r.start_time || '')}</td>
      <td>${window.htmlescape(r.end   || r.end_time   || '')}</td>
      <td><span class="badge ${/cancel/i.test(r.status)?'warn':(/pprov|book/i.test(r.status)?'success':'info')}">${window.htmlescape(r.status)}</span></td>
      <td style="text-align:right;white-space:nowrap;">
        <button class="btn btn-ghost" onclick="openResvModal(${r.id}, '${(r.status||'').toLowerCase()}')">Change</button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="6" class="muted">No reservations found</td></tr>`;
}

function openResvModal(id, st){
  resvId.value = id;
  resvStatus.value = st === 'booked' ? 'approved' : st;
  document.getElementById('resvModal').hidden = false;
}
function closeResvModal(){ document.getElementById('resvModal').hidden = true; }

document.getElementById('resvForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  try{
    await window.api('/reservations/status', { method:'POST', body:{ id: Number(resvId.value), status: resvStatus.value }});
    window.showToast('Status updated');
    closeResvModal(); loadRes();
  }catch(e){ window.showToast(e.message || 'Failed'); }
});

applyFilters.addEventListener('click', loadRes);
window.addEventListener('DOMContentLoaded', loadRes);
</script>
<?php require __DIR__.'/../../layouts/footer.php'; ?>
