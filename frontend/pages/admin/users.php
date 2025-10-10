<?php
$adminArea = true;  
$title = 'Admin • Users';
$active = 'admin';
require __DIR__.'/../../layouts/header.php';
if (($role ?? 'student') !== 'admin') { header("Location: {$BASE_URL}/pages/home.php"); exit; }
?>
<div class="card card-pad">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
    <h1 style="margin:0;">Manage Users</h1>
    <div style="display:flex;gap:8px;">
      <input class="input" id="q" placeholder="Search name/email/ID" style="width:260px">
      <select class="input" id="role"><option value="">Any role</option><option value="student">Student</option><option value="admin">Admin</option></select>
      <button class="btn" id="searchBtn">Search</button>
    </div>
  </div>
  <table class="table" id="usersTbl" style="margin-top:10px;">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Student ID</th><th>Role</th><th>Joined</th><th></th></tr></thead>
    <tbody><tr><td colspan="7" class="muted">Loading…</td></tr></tbody>
  </table>
</div>

<!-- Role Modal -->
<div class="modal-backdrop" id="roleModal" hidden>
  <div class="modal">
    <div class="hd" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
      <h2 id="roleTitle" style="margin:0;font-size:18px;">Change Role</h2>
      <button class="btn btn-ghost" onclick="closeRoleModal()">✕</button>
    </div>
    <div class="bd" style="padding:20px;">
      <form id="roleForm" class="form">
        <input type="hidden" id="roleUserId">
        <div>
          <label class="label">Role</label>
          <select class="input" id="roleValue" required>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;">
          <button type="button" class="btn btn-ghost" onclick="closeRoleModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.modal-backdrop{ position:fixed; inset:0; background:rgba(15,23,42,.45); display:flex; align-items:center; justify-content:center; z-index:80; }
.modal{ background:#fff; border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow); width:420px; max-width:95vw; animation:fadeIn .15s ease-out; }
@keyframes fadeIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
</style>

<script>
async function loadUsers(){
  const params = { q: q.value.trim(), role: role.value };
  const rows = await window.api('/admin/users', { params });
  const tb = document.querySelector('#usersTbl tbody');
  tb.innerHTML = rows.map(u => `
    <tr>
      <td>${u.id}</td>
      <td>${window.htmlescape(u.name||'')}</td>
      <td>${window.htmlescape(u.email||'')}</td>
      <td>${window.htmlescape(u.student_id||'')}</td>
      <td><span class="badge">${window.htmlescape(u.role||'')}</span></td>
      <td>${window.htmlescape(u.created_at||'')}</td>
      <td style="text-align:right;">
        <button class="btn btn-ghost" onclick="openRoleModal(${u.id}, '${u.role}')">Change Role</button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="7" class="muted">No users found</td></tr>`;
}

function openRoleModal(id, current){
  roleUserId.value = id;
  roleValue.value  = current;
  document.getElementById('roleModal').hidden = false;
}
function closeRoleModal(){ document.getElementById('roleModal').hidden = true; }

document.getElementById('roleForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  try{
    await window.api('/admin/users/set-role', { method:'POST', body:{ user_id: Number(roleUserId.value), role: roleValue.value }});
    window.showToast('Role updated');
    closeRoleModal(); loadUsers();
  }catch(e){ window.showToast(e.message || 'Failed'); }
});

document.getElementById('searchBtn').addEventListener('click', loadUsers);
window.addEventListener('DOMContentLoaded', loadUsers);
</script>
<?php require __DIR__.'/../../layouts/footer.php'; ?>
