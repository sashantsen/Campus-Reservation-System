<?php
class Room extends Model {
  protected string $table = 'rooms';

  public function allActive() {
    $st = Database::conn()->query("SELECT * FROM rooms WHERE is_active = 1 ORDER BY name");
    return $st->fetchAll();
  }
}
