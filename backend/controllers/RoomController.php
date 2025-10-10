<?php
class RoomController extends Controller {

  /** List all active rooms (public/student) */
  public function list() {
    $rooms = (new Room())->allActive();
    return $this->json($rooms);
  }

  /** Admin: create a room (name, location, capacity, equipment, open_from, open_to) */
  public function create() {
    Auth::requireAdmin();

    // Read JSON or fallback to form safely
    $raw = file_get_contents('php://input');
    $in  = json_decode($raw, true);
    if (!is_array($in)) $in = $_POST;

    $name      = trim((string)($in['name'] ?? ''));
    $location  = trim((string)($in['location'] ?? ''));
    $capacity  = isset($in['capacity']) ? (int)$in['capacity'] : 0;
    $equipment = trim((string)($in['equipment'] ?? ''));
    $openFrom  = $in['open_from'] ?? null; if ($openFrom === '') $openFrom = null;
    $openTo    = $in['open_to']   ?? null; if ($openTo   === '') $openTo   = null;

    if ($name === '') return $this->json(['error'=>'Name is required'], 422);

    $pdo = Database::conn();
    $st = $pdo->prepare("
      INSERT INTO rooms(name, location, capacity, equipment, is_active, open_from, open_to, created_at)
      VALUES(?,?,?,?,?,?,?,?)
    ");
    $st->execute([
      $name, ($location !== '' ? $location : null), $capacity ?: 0, $equipment,
      1, $openFrom, $openTo, Helpers::now()
    ]);

    return $this->json(['message' => 'Room created', 'id' => (int)$pdo->lastInsertId()], 201);
  }
}
