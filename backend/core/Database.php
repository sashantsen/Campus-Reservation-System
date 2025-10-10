<?php
class Database {
  private static ?PDO $pdo = null;

  public static function conn(): PDO {
    if (!self::$pdo) {
      $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
      $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ];
      self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    }
    return self::$pdo;
  }
}
