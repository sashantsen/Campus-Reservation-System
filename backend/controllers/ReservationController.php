<?php
class ReservationController extends Controller
{
  /**
   * POST /reservations
   * Body: room_id, start_time, end_time (YYYY-MM-DD HH:MM:SS)
   */
  public function create()
  {
    Auth::requireAuth();

    // Optional: block admins from booking rooms
    // if (Auth::role() === 'admin') {
    //   return $this->json(['error' => 'Admins cannot create reservations'], 403);
    // }

    $data = $this->input(['room_id','start_time','end_time'], true);
    $data['user_id'] = Auth::id();

    // --- Validate room: accept id or exact name; resolve to id ---
    $pdo = Database::conn();
    if (ctype_digit((string)$data['room_id'])) {
      $roomId = (int)$data['room_id'];
      $st = $pdo->prepare("SELECT id FROM rooms WHERE id = ? LIMIT 1");
      $st->execute([$roomId]);
      if (!$st->fetchColumn()) {
        return $this->json(['error' => 'Invalid room_id'], 422);
      }
    } else {
      $name = trim((string)$data['room_id']);
      $st = $pdo->prepare("SELECT id FROM rooms WHERE name = ? LIMIT 1");
      $st->execute([$name]);
      $roomId = (int)$st->fetchColumn();
      if (!$roomId) {
        return $this->json(['error' => 'Room not found: ' . $name], 422);
      }
      $data['room_id'] = $roomId; // normalize to numeric id
    }

    // Validate datetime
    foreach (['start_time','end_time'] as $k) {
      if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data[$k])) {
        return $this->json(['error' => "Invalid datetime for $k"], 422);
      }
    }
    if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
      return $this->json(['error' => 'end_time must be after start_time'], 422);
    }

    // Conflict check
    $m = new Reservation();
    if ($m->hasConflict((int)$data['room_id'], $data['start_time'], $data['end_time'])) {
      return $this->json(['error' => 'Time conflict for this room'], 409);
    }

    // Auto-approve so UI shows "Booked"
    $data['status'] = 'approved';

    $id = $m->create($data);
    return $this->json(['message' => 'Reservation created', 'id' => $id], 201);
  }

  /** GET /reservations/me */
  public function mine()
  {
    Auth::requireAuth();
    $rows = (new Reservation())->forUser(Auth::id());
    return $this->json($rows);
  }

  /** POST /reservations/status (admin only) */
  public function updateStatus()
  {
    Auth::requireAdmin();
    $data = $this->input(['id','status'], true);
    $allowed = ['pending','approved','cancelled'];
    if (!in_array($data['status'], $allowed, true)) {
      return $this->json(['error' => 'Invalid status'], 422);
    }
    (new Reservation())->setStatus((int)$data['id'], $data['status']);
    return $this->json(['message' => 'Status updated']);
  }

  /** GET /reservations/all (admin only) */
  public function all()
  {
    Auth::requireAdmin();
    $rows = (new Reservation())->allWithUsersRooms();
    return $this->json($rows);
  }

  /**
   * GET /reservations/upcoming
   * Next 7 days of approved reservations (max 20)
   */
  public function upcoming()
  {
    $pdo = Database::conn();
    $sql = "
      SELECT r.id,
             rm.name AS room,
             r.start_time AS start,
             r.end_time   AS end
      FROM reservations r
      JOIN rooms rm ON rm.id = r.room_id
      WHERE r.status = 'approved'
        AND r.start_time >= NOW()
        AND r.start_time < DATE_ADD(NOW(), INTERVAL 7 DAY)
      ORDER BY r.start_time ASC
      LIMIT 20
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $this->json($rows);
  }

  /**
   * GET /reservations
   * Optional: q, status, range (today|week|month), &scope=all (admin)
   */
  public function index()
  {
    Auth::requireAuth();
    $userId = Auth::id();
    $role   = Auth::role();

    $q      = $_GET['q']      ?? '';
    $status = $_GET['status'] ?? '';
    $range  = $_GET['range']  ?? '';
    $scope  = $_GET['scope']  ?? 'me';

    $pdo = Database::conn();
    $params = [];
    $where  = [];

    $joinUser = false;
    if ($scope !== 'all' || $role !== 'admin') {
      $where[] = 'r.user_id = ?';
      $params[] = $userId;
    } else {
      $joinUser = true; // admin: show who booked
    }

    if ($status) {
      $map = [
        'Booked'=>'approved','Approved'=>'approved',
        'Cancelled'=>'cancelled','Pending'=>'pending','CheckedIn'=>'approved'
      ];
      $dbStatus = $map[$status] ?? strtolower($status);
      $where[]  = 'r.status = ?';
      $params[] = $dbStatus;
    }

    if ($q) {
      $where[] = 'rm.name LIKE ?';
      $params[] = '%' . $q . '%';
    }

    if ($range === 'today') {
      $where[] = 'DATE(r.start_time) = CURDATE()';
    } elseif ($range === 'week') {
      $where[] = 'YEARWEEK(r.start_time, 1) = YEARWEEK(CURDATE(), 1)';
    } elseif ($range === 'month') {
      $where[] = 'YEAR(r.start_time) = YEAR(CURDATE()) AND MONTH(r.start_time) = MONTH(CURDATE())';
    }

    $checkedCol = self::hasCheckedInColumn() ? ", r.checked_in" : "";
    $userCols   = $joinUser ? ", u.name AS user_name, u.email AS user_email" : "";
    $userJoin   = $joinUser ? "JOIN users u ON u.id = r.user_id" : "";

    $sql = "
      SELECT r.id, rm.name AS room, r.start_time AS start, r.end_time AS end, r.status
           $checkedCol
           $userCols
      FROM reservations r
      JOIN rooms rm ON rm.id = r.room_id
      $userJoin
      " . (count($where) ? 'WHERE ' . implode(' AND ', $where) : '') . "
      ORDER BY r.start_time DESC
      LIMIT 200
    ";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$r) {
      if ($r['status'] === 'approved') {
        $r['status'] = (self::hasCheckedInColumn() && !empty($r['checked_in'])) ? 'CheckedIn' : 'Booked';
      } elseif ($r['status'] === 'cancelled') {
        $r['status'] = 'Cancelled';
      } elseif ($r['status'] === 'pending') {
        $r['status'] = 'Pending';
      } else {
        $r['status'] = ucfirst($r['status']);
      }
      unset($r['checked_in']);
    }

    return $this->json($rows);
  }

  /** GET /reservations/get?id=123 */
  public function show()
  {
    Auth::requireAuth();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) return $this->json(['error' => 'Missing id'], 422);

    $pdo = Database::conn();
    $st = $pdo->prepare("
      SELECT r.*, rm.name AS room
      FROM reservations r
      JOIN rooms rm ON rm.id = r.room_id
      WHERE r.id = ?
      LIMIT 1
    ");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return $this->json(['error' => 'Not found'], 404);

    if (Auth::role() !== 'admin' && (int)$row['user_id'] !== (int)Auth::id()) {
      return $this->json(['error' => 'Forbidden'], 403);
    }

    if (($row['status'] ?? '') === 'approved') {
      $label = (self::hasCheckedInColumn() && !empty($row['checked_in'])) ? 'CheckedIn' : 'Booked';
    } elseif (($row['status'] ?? '') === 'cancelled') {
      $label = 'Cancelled';
    } elseif (($row['status'] ?? '') === 'pending') {
      $label = 'Pending';
    } else {
      $label = ucfirst((string)$row['status']);
    }

    return $this->json([
      'id'     => (int)$row['id'],
      'room'   => $row['room'], // fixed key name
      'start'  => $row['start_time'],
      'end'    => $row['end_time'],
      'status' => $label,
      'user'   => null,
      'notes'  => $row['notes'] ?? null
    ]);
  }

  /** POST /reservations/checkin { id } */
  public function checkin()
  {
    Auth::requireAuth();
    $data = $this->input(['id'], true);
    $id = (int)$data['id'];

    $pdo = Database::conn();
    $st = $pdo->prepare("SELECT user_id FROM reservations WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $owner = $st->fetchColumn();
    if (!$owner) return $this->json(['error' => 'Not found'], 404);
    if (Auth::role() !== 'admin' && (int)$owner !== (int)Auth::id()) {
      return $this->json(['error' => 'Forbidden'], 403);
    }

    if (self::hasCheckedInColumn()) {
      $pdo->prepare("UPDATE reservations SET checked_in=1 WHERE id=?")->execute([$id]);
    } else {
      $pdo->prepare("UPDATE reservations SET status='approved' WHERE id=?")->execute([$id]);
    }

    return $this->json(['message' => 'Checked in']);
  }

  /** POST /reservations/cancel { id } */
  public function cancel()
  {
    Auth::requireAuth();
    $data = $this->input(['id'], true);
    $id = (int)$data['id'];

    $pdo = Database::conn();
    $st = $pdo->prepare("SELECT user_id FROM reservations WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $owner = $st->fetchColumn();
    if (!$owner) return $this->json(['error' => 'Not found'], 404);
    if (Auth::role() !== 'admin' && (int)$owner !== (int)Auth::id()) {
      return $this->json(['error' => 'Forbidden'], 403);
    }

    $pdo->prepare("UPDATE reservations SET status='cancelled' WHERE id=?")->execute([$id]);
    return $this->json(['message' => 'Cancelled']);
  }

  /** Utility: check if reservations.checked_in exists */
  private static function hasCheckedInColumn(): bool
  {
    static $has = null;
    if ($has !== null) return $has;
    try {
      $pdo = Database::conn();
      $stmt = $pdo->query("SHOW COLUMNS FROM reservations LIKE 'checked_in'");
      $has = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
      $has = false;
    }
    return $has;
  }
}
