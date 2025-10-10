<?php
$adminArea = true;  
$title = 'Admin • Dashboard';
$active = 'admin';
require __DIR__.'/../../layouts/header.php';

if (($role ?? 'student') !== 'admin') {
  header("Location: {$BASE_URL}/pages/home.php"); exit;
}
?>
<div class="stack">
  <div class="card card-pad">
    <h1 style="margin:0 0 12px 0;">Admin Dashboard</h1>
    <div class="kpis" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;">
      <div class="card kpi"><div class="kpi-title">Users</div><div id="kpi-users" class="kpi-value">—</div></div>
      <div class="card kpi"><div class="kpi-title">Admins</div><div id="kpi-admins" class="kpi-value">—</div></div>
      <div class="card kpi"><div class="kpi-title">Active Rooms</div><div id="kpi-rooms" class="kpi-value">—</div></div>
      <div class="card kpi"><div class="kpi-title">Pending</div><div id="kpi-pending" class="kpi-value">—</div></div>
    </div>
  </div>

  <div class="grid-2">
    <div class="card card-pad">
      <h2 style="margin:0 0 10px 0;">Bookings — Last 14 Days</h2>
      <canvas id="chartBookings" height="140"></canvas>
    </div>
    <div class="card card-pad">
      <h2 style="margin:0 0 10px 0;">Status Breakdown</h2>
      <canvas id="chartStatus" height="140"></canvas>
    </div>
  </div>

  <div class="card card-pad">
    <h2 style="margin:0 0 10px 0;">Recent Activity</h2>
    <table class="table" id="recentTbl">
      <thead><tr><th>User</th><th>Room</th><th>Start</th><th>End</th><th>Status</th><th>Created</th></tr></thead>
      <tbody><tr><td colspan="6" class="muted">Loading…</td></tr></tbody>
    </table>
  </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', async () => {
  // KPIs + Recent
  try {
    const d = await window.api('/admin/dashboard');
    document.getElementById('kpi-users').textContent   = d.users ?? 0;
    document.getElementById('kpi-admins').textContent  = d.admins ?? 0;
    document.getElementById('kpi-rooms').textContent   = d.rooms_active ?? 0;
    document.getElementById('kpi-pending').textContent = d.pending ?? 0;

    const tb = document.querySelector('#recentTbl tbody');
    tb.innerHTML = (d.recent || []).map(r => `
      <tr>
        <td>${window.htmlescape(r.user)}</td>
        <td>${window.htmlescape(r.room)}</td>
        <td>${window.htmlescape(r.start_time)}</td>
        <td>${window.htmlescape(r.end_time)}</td>
        <td><span class="badge ${/cancel/i.test(r.status)?'warn':(/pprov/i.test(r.status)?'success':'info')}">${window.htmlescape(r.status)}</span></td>
        <td>${window.htmlescape(r.created_at)}</td>
      </tr>
    `).join('') || `<tr><td colspan="6" class="muted">No activity yet</td></tr>`;
  } catch (e) {
    window.showToast('Failed to load dashboard');
  }

  // Charts: aggregate from /reservations?scope=all (last 200)
  try {
    const rows = await window.api('/reservations', { params: { scope:'all' }});
    const byDate = {};
    const statusCount = { Approved:0, Booked:0, Pending:0, Cancelled:0, CheckedIn:0 };

    const today = new Date(); today.setHours(0,0,0,0);
    for (let i=13; i>=0; i--) {
      const d = new Date(today); d.setDate(today.getDate() - i);
      const key = d.toISOString().slice(0,10);
      byDate[key] = 0;
    }

    (rows||[]).forEach(r=>{
      const day = (r.start || r.start_time || '').slice(0,10);
      if (byDate[day] !== undefined) byDate[day] += 1;
      const st = (r.status||'').toString();
      if (statusCount[st] !== undefined) statusCount[st] += 1;
    });

    const labels = Object.keys(byDate);
    const data   = labels.map(k => byDate[k]);

    if (window.Chart) {
      const ctx1 = document.getElementById('chartBookings').getContext('2d');
      new Chart(ctx1, {
        type: 'line',
        data: { labels, datasets: [{ label:'Bookings', data }] },
        options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
      });

      const ctx2 = document.getElementById('chartStatus').getContext('2d');
      new Chart(ctx2, {
        type: 'doughnut',
        data: {
          labels: Object.keys(statusCount),
          datasets: [{ data: Object.values(statusCount) }]
        },
        options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
      });
    }
  } catch(e){
    console.warn('Chart load failed', e);
  }
});
</script>
<?php require __DIR__.'/../../layouts/footer.php'; ?>
