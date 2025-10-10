<?php
$title  = 'Dashboard';
$active = 'home';
require __DIR__ . '/../layouts/header.php';
?>
<div class="stack">
  <h1>Dashboard</h1>

  <section class="kpis">
    <div class="card kpi"><div class="kpi-title">Today’s Bookings</div><div id="kpi-today" class="kpi-value">—</div></div>
    <div class="card kpi"><div class="kpi-title">Occupancy</div><div id="kpi-occ" class="kpi-value">—</div></div>
    <div class="card kpi"><div class="kpi-title">Active Rooms</div><div id="kpi-rooms" class="kpi-value">—</div></div>
    <div class="card kpi"><div class="kpi-title">No-shows</div><div id="kpi-noshow" class="kpi-value">—</div></div>
  </section>

  <section class="grid-2">
    <div class="card card-pad">
      <h2>Bookings Trend</h2>
      <canvas id="bookingTrend" height="120"></canvas>
    </div>
    <div class="card card-pad">
      <h2>Upcoming Reservations</h2>
      <div id="upcoming" class="muted">Loading…</div>
    </div>
  </section>
</div>

<script>
window.addEventListener('DOMContentLoaded', async () => {
  // ---- KPIs ----
  try {
    const kpi = await window.api('/metrics/kpi');
    document.getElementById('kpi-today').textContent  = kpi.today ?? 0;
    document.getElementById('kpi-occ').textContent    = (kpi.occupancy ?? 0) + '%';
    document.getElementById('kpi-rooms').textContent  = kpi.rooms ?? 0;
    document.getElementById('kpi-noshow').textContent = kpi.noshows ?? 0;
  } catch (e) {
    console.error('KPI load failed:', e);
  }

  // ---- Upcoming reservations ----
  const box = document.getElementById('upcoming');
  try {
    const resv = await window.api('/reservations/upcoming');
    if (!resv || !resv.length) {
      box.innerHTML = 'No upcoming reservations yet — <a href="<?= $BASE_URL ?>/pages/rooms/index.php">book a room</a>.';
    } else {
      box.innerHTML = resv.slice(0, 5).map(r =>
        `<div><strong>${window.htmlescape(r.room)}</strong> — ${window.htmlescape(r.start)}–${window.htmlescape(r.end)}</div>`
      ).join('');
    }
  } catch (e) {
    box.textContent = 'Failed to load';
  }

  // ---- Chart (dummy data for now) ----
  const el = document.getElementById('bookingTrend');
  if (el && window.Chart) {
    const ctx = el.getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets: [{ label: 'Bookings', data: [5,7,6,9,8,4,3] }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
