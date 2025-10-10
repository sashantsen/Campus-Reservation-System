<?php
class HomeController extends Controller
{
  public function index() {
    $this->json(['message' => 'Campus Reservation API is running']);
  }

  // GET /metrics/kpi
  public function kpi() {
    $pdo = Database::conn();
    $today = (new DateTime('today'))->format('Y-m-d');

    // 1) Bookings today (pending|approved)
    $stmt = $pdo->prepare(
      "SELECT COUNT(*) FROM reservations
       WHERE DATE(start_time)=:d AND status IN ('pending','approved')"
    );
    $stmt->execute([':d' => $today]);
    $todayBookings = (int)$stmt->fetchColumn();

    // 2) Active rooms
    $rooms = (int)$pdo->query(
      "SELECT COUNT(*) FROM rooms WHERE is_active=1"
    )->fetchColumn();

    // 3) Occupancy (% of rooms with an approved booking today)
    $occ = 0;
    if ($rooms > 0) {
      $stmt = $pdo->prepare(
        "SELECT COUNT(DISTINCT room_id)
         FROM reservations
         WHERE DATE(start_time)=:d AND status='approved'"
      );
      $stmt->execute([':d' => $today]);
      $roomsBookedToday = (int)$stmt->fetchColumn();
      $occ = (int)round(($roomsBookedToday / $rooms) * 100);
    }

    // 4) No-shows (if 'checked_in' exists)
    $noShows = 0;
    try {
      $noShows = (int)$pdo->query(
        "SELECT COUNT(*) FROM reservations
         WHERE status='approved' AND start_time < NOW()
           AND COALESCE(checked_in,0)=0"
      )->fetchColumn();
    } catch (\Throwable $e) {
      // column may not exist – keep 0
    }

    $this->json([
      'today'     => $todayBookings,
      'occupancy' => $occ,
      'rooms'     => $rooms,
      'noshows'   => $noShows,
    ]);
  }
}
