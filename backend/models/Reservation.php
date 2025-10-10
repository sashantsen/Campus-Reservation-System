<?php
class Reservation extends Model
{
  protected string $table = 'reservations';

  /** Check time overlap for a room excluding cancelled */
  public function hasConflict(int $roomId, string $start, string $end): bool
  {
    $pdo = Database::conn();
    $sql = "
      SELECT 1
      FROM reservations
      WHERE room_id = ?
        AND status IN ('pending','approved')       -- still blocks during pending/approved
        AND NOT (end_time <= ? OR start_time >= ?) -- true overlap
      LIMIT 1
    ";
    $st = $pdo->prepare($sql);
    $st->execute([$roomId, $start, $end]);
    return (bool) $st->fetchColumn();
  }

  /** Create a reservation (returns new id) */
  public function create(array $data): int
  {
    $pdo = Database::conn();
    $sql = "
      INSERT INTO reservations (user_id, room_id, start_time, end_time, status, created_at)
      VALUES (?, ?, ?, ?, ?, ?)
    ";
    $st = $pdo->prepare($sql);
    $st->execute([
      (int)$data['user_id'],
      (int)$data['room_id'],
      $data['start_time'],              // 'YYYY-MM-DD HH:MM:SS'
      $data['end_time'],
      $data['status'] ?? 'pending',     // controller now passes 'approved'
      Helpers::now()                    // ensure Y-m-d H:i:s
    ]);
    return (int)$pdo->lastInsertId();
  }

  /** Reservations for a user (latest first) */
  public function forUser(int $userId): array
  {
    $pdo = Database::conn();
    $sql = "
      SELECT r.*, rm.name AS room_name, rm.location, rm.capacity
      FROM reservations r
      JOIN rooms rm ON rm.id = r.room_id
      WHERE r.user_id = ?
      ORDER BY r.start_time DESC
    ";
    $st = $pdo->prepare($sql);
    $st->execute([(int)$userId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  /** Update status */
  public function setStatus(int $id, string $status): void
  {
    $pdo = Database::conn();
    $st = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $st->execute([$status, (int)$id]);
  }

  /** Admin: list all with user + room info */
  public function allWithUsersRooms(): array
  {
    $pdo = Database::conn();
    $sql = "
      SELECT r.*, u.name AS user_name, rm.name AS room_name
      FROM reservations r
      JOIN users u ON u.id = r.user_id
      JOIN rooms rm ON rm.id = r.room_id
      ORDER BY r.start_time DESC
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }
}
