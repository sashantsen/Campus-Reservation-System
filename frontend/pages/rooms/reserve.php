<?php
$title = 'Reserve a Room';
$active = 'rooms';
require __DIR__ . '/../../layouts/header.php';
$room = $_GET['room'] ?? '';
?>
<div class="card card-pad" style="max-width:760px; margin:0 auto;">
  <h1 style="margin-top:0;">Reserve</h1>
  <form class="form" id="reserveForm" novalidate>
    <div class="form" style="grid-template-columns:1fr 1fr; gap:12px;">
      <div>
        <label class="label">Room</label>
        <input class="input" id="roomName"
               value="<?= htmlspecialchars($room, ENT_QUOTES) ?>"
               placeholder="Library-101 (exact)"
               list="roomOptions" required>
        <datalist id="roomOptions"></datalist>
        <input type="hidden" id="room_id">
        <div id="roomError" style="color:#dc2626; font-size:12px; margin-top:6px; display:none;">
          Invalid room. Pick a room from the list.
        </div>
      </div>
      <div>
        <label class="label">Date</label>
        <input class="input" type="date" id="date" required>
      </div>
      <div>
        <label class="label">Start time</label>
        <input class="input" type="time" id="start" required>
      </div>
      <div>
        <label class="label">End time</label>
        <input class="input" type="time" id="end" required>
      </div>
    </div>

    <div>
      <label class="label">Notes (optional)</label>
      <textarea class="input" rows="3" id="notes" placeholder="Any special needs?"></textarea>
    </div>

    <button class="btn btn-primary" id="submitBtn" type="submit" disabled>
      Confirm reservation
    </button>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const elForm   = document.getElementById('reserveForm');
  const roomName = document.getElementById('roomName');
  const roomId   = document.getElementById('room_id');
  const elDate   = document.getElementById('date');
  const elStart  = document.getElementById('start');
  const elEnd    = document.getElementById('end');
  const elNotes  = document.getElementById('notes');
  const btn      = document.getElementById('submitBtn');
  const dl       = document.getElementById('roomOptions');
  const roomError= document.getElementById('roomError');

  // Add a quick red border when invalid (no CSS dependency)
  const markInvalid = () => { roomName.style.borderColor = '#dc2626'; };
  const clearInvalid= () => { roomName.style.borderColor = ''; };

  // Canonicalize names (case/space/dash/punctuation tolerant)
  const canon = s => (s || '')
    .toLowerCase()
    .normalize('NFKC')
    .replace(/[\s\-_–—]+/g, '')
    .replace(/[^\p{L}\p{N}]/gu, '');

  let rooms = [];
  let canonMap = new Map();

  const showError = (msg) => {
    roomError.textContent = msg || 'Invalid room. Pick a room from the list.';
    roomError.style.display = 'block';
    markInvalid();
  };
  const hideError = () => {
    roomError.style.display = 'none';
    clearInvalid();
  };

  try {
    // Load rooms (expects /rooms -> [{id,name,...}])
    rooms = await window.api('/rooms');
    dl.innerHTML = rooms.map(r => `<option value="${r.name}"></option>`).join('');
    canonMap.clear();
    rooms.forEach(r => canonMap.set(canon(r.name), r));

    // Pre-fill from query (?room=) robustly
    const initVal = (roomName.value || '').trim();
    const match = canonMap.get(canon(initVal));
    if (match) { roomId.value = match.id; btn.disabled = false; hideError(); }
    else if (initVal) { btn.disabled = true; showError('Room not found: ' + initVal); }
  } catch (e) {
    console.error('Failed to load rooms', e);
  }

  // Validate input (exact canonical match; fallback to /rooms?q=)
  const validateRoom = async () => {
    const raw = roomName.value.trim();
    const v = canon(raw);

    if (!v) {
      roomId.value = '';
      btn.disabled = true;
      hideError();
      return false;
    }

    let match = canonMap.get(v);

    // Fallback: query backend in case list changed
    if (!match) {
      try {
        const list = await window.api('/rooms', { params: { q: raw } });
        const byCanon = (list || []).find(r => canon(r.name) === v);
        if (byCanon) match = byCanon;
      } catch (e) {
        console.warn('Lookup failed', e);
      }
    }

    if (match) {
      roomId.value = match.id;
      btn.disabled = false;
      hideError();
      return true;
    }

    roomId.value = '';
    btn.disabled = true;
    showError('Room not found: ' + raw);
    return false;
  };

  roomName.addEventListener('input', () => { validateRoom(); });
  roomName.addEventListener('blur',  () => { validateRoom(); });

  const toDateTime = (d, t) => `${d} ${t}:00`;

  elForm.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const ok = await validateRoom();
      if (!ok) throw new Error('Please select a valid room (exact name).');
      if (!elDate.value || !elStart.value || !elEnd.value) throw new Error('Please fill all fields.');
      const start_time = toDateTime(elDate.value, elStart.value);
      const end_time   = toDateTime(elDate.value, elEnd.value);
      if (new Date(start_time) >= new Date(end_time)) throw new Error('End time must be after start.');

      await window.api('/reservations', {
        method: 'POST',
        body: {
          room_id: Number(roomId.value),
          start_time,
          end_time,
          notes: elNotes.value.trim()
        }
      });

      window.showToast('Reservation created');
      setTimeout(() => location.href = "<?= $BASE_URL ?>/pages/reservations/index.php", 800);
    } catch (err) {
      window.showToast(err.message || 'Failed to reserve');
      console.error(err);
    }
  });
});
</script>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
