<?php
class AdminController extends Controller {

  /** KPI cards for admin dashboard */
  public function dashboard() {
    Auth::requireAdmin();
    $pdo = Database::conn();

    $stats = [
      'users'        => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
      'students'     => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
      'admins'       => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn(),
      'rooms_active' => (int)$pdo->query("SELECT COUNT(*) FROM rooms WHERE is_active=1")->fetchColumn(),
      'rooms_total'  => (int)$pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
      'resv_total'   => (int)$pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
      'pending'      => (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status='pending'")->fetchColumn(),
      'approved'     => (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status='approved'")->fetchColumn(),
      'cancelled'    => (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status='cancelled'")->fetchColumn(),
    ];

    $stmt = $pdo->query("
      SELECT r.id, rm.name AS room, u.name AS user, r.start_time, r.end_time, r.status, r.created_at
      FROM reservations r
      JOIN rooms rm ON rm.id=r.room_id
      JOIN users u ON u.id=r.user_id
      ORDER BY r.created_at DESC
      LIMIT 10
    ");
    $stats['recent'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $this->json($stats);
  }

  /** Users list + search/filter (admin only) */
  public function users() {
    Auth::requireAdmin();
    $q    = trim($_GET['q'] ?? '');
    $role = trim($_GET['role'] ?? '');

    $pdo = Database::conn();
    $where = [];
    $p = [];

    if ($q !== '') {
      $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.student_id LIKE ?)";
      $p[] = "%$q%"; $p[] = "%$q%"; $p[] = "%$q%";
    }
    if ($role !== '') {
      $where[] = "u.role = ?";
      $p[] = $role;
    }

    $sql = "
      SELECT u.id, u.name, u.email, u.student_id, u.role, u.created_at
      FROM users u
      " . (count($where) ? "WHERE ".implode(' AND ', $where) : "") . "
      ORDER BY u.created_at DESC
      LIMIT 500
    ";
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return $this->json($st->fetchAll(PDO::FETCH_ASSOC));
  }

  /** Promote/demote user role */
  public function setUserRole() {
    Auth::requireAdmin();
    $in = $this->input(['user_id','role'], true);
    $userId = (int)$in['user_id'];
    $role   = trim(strtolower($in['role']));

    if (!in_array($role, ['admin','student'], true)) {
      return $this->json(['error' => 'Invalid role'], 422);
    }
    $pdo = Database::conn();
    $st = $pdo->prepare("UPDATE users SET role=? WHERE id=?");
    $st->execute([$role, $userId]);

    if (Auth::id() === $userId) {
      $u = $pdo->query("SELECT * FROM users WHERE id=".$userId)->fetch(PDO::FETCH_ASSOC);
      if ($u) Auth::login($u);
    }

    return $this->json(['message' => 'Role updated']);
  }

  /** Rooms list (all, including inactive) + simple filters */
  public function rooms() {
    Auth::requireAdmin();
    $q        = trim($_GET['q'] ?? '');
    $active   = $_GET['active'] ?? ''; // '', '1', '0'
    $pdo = Database::conn();

    $where = [];
    $p = [];
    if ($q !== '') { $where[] = "(name LIKE ? OR location LIKE ?)"; $p[]="%$q%"; $p[]="%$q%"; }
    if ($active === '1' || $active === '0') { $where[]="is_active=?"; $p[]=(int)$active; }

    $sql = "
      SELECT id, name, location, capacity, equipment, is_active, open_from, open_to, created_at, updated_at
      FROM rooms
      " . (count($where) ? "WHERE ".implode(' AND ', $where) : "") . "
      ORDER BY name ASC
      LIMIT 500
    ";
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return $this->json($st->fetchAll(PDO::FETCH_ASSOC));
  }

  /** Update a room (name/location/capacity/equipment/open_from/open_to/is_active) */
  public function updateRoom() {
    Auth::requireAdmin();

    // Read raw JSON or form data
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = $_POST;

    $id = isset($data['id']) ? (int)$data['id'] : 0;
    if (!$id) return $this->json(['error'=>'Missing id'], 422);

    // Normalize time strings to HH:MM:SS or NULL
    $normTime = function($v) {
      if ($v === '' || $v === null) return null;
      $v = trim((string)$v);
      if (preg_match('/^\d{2}:\d{2}$/', $v)) return $v . ':00';
      if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $v)) return $v;
      return null; // anything else -> NULL
    };

    $fields = [];
    $vals   = [];

    $allowed = ['name','location','capacity','equipment','open_from','open_to','is_active'];
    foreach ($allowed as $k) {
      if (!array_key_exists($k, $data)) continue;   // only change what was sent

      $v = $data[$k];

      if ($k === 'capacity')  $v = (int)$v;
      if ($k === 'is_active') $v = (int)$v;
      if ($k === 'open_from') $v = $normTime($v);
      if ($k === 'open_to')   $v = $normTime($v);

      // convert empty strings to NULL for strings
      if (in_array($k, ['name','location','equipment'], true) && $v === '') $v = null;

      $fields[] = "$k = ?";
      $vals[]   = $v;
    }

    if (!$fields) return $this->json(['error'=>'No valid fields provided'], 422);

    $fields[] = "updated_at = ?";
    $vals[]   = Helpers::now();
    $vals[]   = $id;

    $sql = "UPDATE rooms SET ".implode(', ', $fields)." WHERE id = ?";
    $st  = Database::conn()->prepare($sql);
    $st->execute($vals);

    return $this->json(['message' => $st->rowCount() ? 'Room updated successfully' : 'No changes (values identical?)']);
  }

  /** Toggle active (archive/unarchive) — JSON + form safe */
  public function toggleRoom() {
    Auth::requireAdmin();

    $raw = file_get_contents('php://input');
    $in  = json_decode($raw, true);
    if (!is_array($in)) $in = $_POST;

    $id = isset($in['id']) ? (int)$in['id'] : 0;
    $isActive = isset($in['is_active']) ? (int)$in['is_active'] : null;
    if (!$id || $isActive === null) return $this->json(['error'=>'Missing id or is_active'], 422);

    $st = Database::conn()->prepare("UPDATE rooms SET is_active=?, updated_at=? WHERE id=?");
    $st->execute([$isActive, Helpers::now(), $id]);
    return $this->json(['message'=>'Room state updated']);
  }
}
