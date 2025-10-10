<?php
$title = 'Rooms';
$active = 'rooms';
require __DIR__ . '/../../layouts/header.php';
?>
<div class="stack">
  <div class="card card-pad">
    <h1 style="margin:0 0 10px 0;">Rooms</h1>

    <div class="form" style="grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:10px;">
      <input class="input" id="q" placeholder="Search by name">
      <select class="input" id="cap">
        <option value="">Any capacity</option>
        <option>1-2</option>
        <option>3-5</option>
        <option>6-10</option>
        <option>10+</option>
      </select>
      <select class="input" id="equip">
        <option value="">Equipment</option>
        <option>Projector</option>
        <option>Whiteboard</option>
      </select>
      <a class="btn btn-primary" href="<?= $BASE_URL ?>/pages/rooms/reserve.php">Reserve</a>
    </div>

    <table class="table" id="roomsTable">
      <thead><tr><th>Room</th><th>Capacity</th><th>Equipment</th><th>Status</th><th></th></tr></thead>
      <tbody><tr><td colspan="5" class="muted">Loading…</td></tr></tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const q = document.getElementById('q');
  const cap = document.getElementById('cap');
  const equip = document.getElementById('equip');
  const tb = document.querySelector('#roomsTable tbody');

  async function loadRooms() {
    try {
      const rooms = await window.api('/rooms'); // GET /api/rooms
      const filtered = rooms.filter(r => {
        const nameOk = !q.value || (r.name || '').toLowerCase().includes(q.value.toLowerCase());
        const capOk = !cap.value || (
          (cap.value === '1-2' && r.capacity >= 1 && r.capacity <= 2) ||
          (cap.value === '3-5' && r.capacity >= 3 && r.capacity <= 5) ||
          (cap.value === '6-10' && r.capacity >= 6 && r.capacity <= 10) ||
          (cap.value === '10+' && r.capacity >= 10)
        );
        const eqOk = !equip.value || (r.equipment && r.equipment.includes(equip.value));
        return nameOk && capOk && eqOk;
      });

      if (!filtered.length) {
        tb.innerHTML = '<tr><td colspan="5" class="muted">No rooms found.</td></tr>';
        return;
      }

      tb.innerHTML = filtered.map(r => `
        <tr>
          <td>${window.htmlescape(r.name)}</td>
          <td>${window.htmlescape(r.capacity)}</td>
          <td>${window.htmlescape(window.normEquip(r.equipment).join(', '))}</td>
          <td><span class="badge ${r.is_active ? 'info' : 'warn'}">${r.is_active ? 'Available' : 'Booked'}</span></td>
          <td><a class="btn btn-ghost" href="<?= $BASE_URL ?>/pages/rooms/reserve.php?room=${encodeURIComponent(r.name)}">Book</a></td>
        </tr>
      `).join('');
    } catch (err) {
      tb.innerHTML = '<tr><td colspan="5" class="muted">Failed to load rooms.</td></tr>';
      console.error(err);
    }
  }

  [q, cap, equip].forEach(el => el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', loadRooms));
  loadRooms();
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
