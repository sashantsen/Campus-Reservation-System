<?php
$adminArea = true;  
$title='Admin • Rooms'; 
$active='admin';
require __DIR__.'/../../layouts/header.php';
if (($role ?? 'student') !== 'admin') { header("Location: {$BASE_URL}/pages/home.php"); exit; }
?>
<div class="stack">
  <div class="card card-pad">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
      <h1 style="margin:0;">Manage Rooms</h1>
      <div style="display:flex;gap:8px;">
        <input class="input" id="rq" placeholder="Search name/location" style="width:240px">
        <select class="input" id="ractive">
          <option value="">All</option><option value="1">Active</option><option value="0">Inactive</option>
        </select>
        <button class="btn" id="rsearch">Search</button>
        <button class="btn btn-primary" id="newRoomBtn">New Room</button>
      </div>
    </div>
    <table class="table" id="admRooms" style="margin-top:10px;">
      <thead><tr><th>Name</th><th>Location</th><th>Capacity</th><th>Equipment</th><th>Open</th><th>Close</th><th>Active</th><th></th></tr></thead>
      <tbody><tr><td colspan="8" class="muted">Loading…</td></tr></tbody>
    </table>
  </div>
</div>

<!-- ===== Room Modal ===== -->
<div class="modal-backdrop" id="roomModal" hidden>
  <div class="modal">
    <div class="hd" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
      <h2 id="modalTitle" style="margin:0;font-size:18px;">Edit Room</h2>
      <button class="btn btn-ghost" onclick="closeModal()">✕</button>
    </div>
    <div class="bd" style="padding:20px;">
      <form id="roomForm" class="form">
        <input type="hidden" id="roomId">
        <div>
          <label class="label">Name</label>
          <input class="input" id="roomName" required>
        </div>
        <div>
          <label class="label">Location</label>
          <input class="input" id="roomLocation">
        </div>
        <div>
          <label class="label">Capacity</label>
          <input class="input" type="number" id="roomCapacity" min="0">
        </div>
        <div>
          <label class="label">Equipment</label>
          <input class="input" id="roomEquip">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div>
            <label class="label">Open From</label>
            <input class="input" type="time" id="roomOpenFrom">
          </div>
          <div>
            <label class="label">Open To</label>
            <input class="input" type="time" id="roomOpenTo">
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;">
          <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.modal-backdrop{
  position:fixed; inset:0; background:rgba(15,23,42,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:80;
}
.modal{
  background:#fff; border:1px solid var(--border); border-radius:16px;
  box-shadow:var(--shadow); width:520px; max-width:95vw;
  animation:fadeIn .15s ease-out;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
</style>

<script>
let editing = false;

async function loadRooms(){
  const params = { q: document.getElementById('rq').value.trim(), active: document.getElementById('ractive').value };
  const rows = await window.api('/admin/rooms', { params });
  const tb = document.querySelector('#admRooms tbody');
  tb.innerHTML = rows.map(r => `
    <tr>
      <td>${window.htmlescape(r.name||'')}</td>
      <td>${window.htmlescape(r.location||'')}</td>
      <td>${window.htmlescape(String(r.capacity ?? 0))}</td>
      <td>${window.htmlescape(r.equipment||'')}</td>
      <td>${r.open_from || '—'}</td>
      <td>${r.open_to || '—'}</td>
      <td>${r.is_active ? '<span class="badge success">Yes</span>' : '<span class="badge warn">No</span>'}</td>
      <td style="text-align:right;white-space:nowrap;">
        <button class="btn btn-ghost" onclick='openEdit(${JSON.stringify(r.id)}, ${JSON.stringify(r)})'>Edit</button>
        <button class="btn ${r.is_active? '':'btn-primary'}" onclick="toggleRoom(${r.id}, ${r.is_active?0:1})">${r.is_active?'Archive':'Activate'}</button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="8" class="muted">No rooms</td></tr>`;
}

function openModal(){ document.getElementById('roomModal').hidden = false; }
function closeModal(){ document.getElementById('roomModal').hidden = true; editing = false; }

function clearForm(){
  document.getElementById('roomForm').reset();
  document.getElementById('roomId').value = '';
}

function openNew(){
  clearForm(); editing = false;
  document.getElementById('modalTitle').textContent = 'New Room';
  openModal();
}

function openEdit(id, r){
  editing = true;
  document.getElementById('modalTitle').textContent = 'Edit Room';
  document.getElementById('roomId').value = r.id;
  document.getElementById('roomName').value = r.name || '';
  document.getElementById('roomLocation').value = r.location || '';
  document.getElementById('roomCapacity').value = r.capacity || 0;
  document.getElementById('roomEquip').value = r.equipment || '';
  document.getElementById('roomOpenFrom').value = (r.open_from || '').substring(0,5);
  document.getElementById('roomOpenTo').value   = (r.open_to || '').substring(0,5);
  openModal();
}

document.getElementById('roomForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const id = document.getElementById('roomId').value;
  const body = {
    id, name:roomName.value.trim(),
    location:roomLocation.value.trim(),
    capacity:Number(roomCapacity.value),
    equipment:roomEquip.value.trim(),
    open_from:roomOpenFrom.value || null,
    open_to:roomOpenTo.value || null
  };
  try{
    if(editing){
      await window.api('/admin/rooms/update',{method:'POST',body});
      window.showToast('Room updated');
    }else{
      await window.api('/rooms',{method:'POST',body});
      window.showToast('Room created');
    }
    closeModal(); loadRooms();
  }catch(e){ window.showToast(e.message || 'Failed'); }
});

async function toggleRoom(id,is_active){
  try{
    await window.api('/admin/rooms/toggle',{method:'POST',body:{id,is_active}});
    window.showToast('Room state changed'); loadRooms();
  }catch(e){ window.showToast(e.message||'Failed'); }
}

document.getElementById('rsearch').addEventListener('click',loadRooms);
document.getElementById('newRoomBtn').addEventListener('click',openNew);
window.addEventListener('DOMContentLoaded',loadRooms);
</script>
<?php require __DIR__.'/../../layouts/footer.php'; ?>
