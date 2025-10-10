<?php
class Helpers {
  public static function now(): string {
    return date('Y-m-d H:i:s');
  }
  public static function isPost(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
  }
  public static function isGet(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
  }
  public static function requireJson(): void {
    header('Content-Type: application/json');
  }
  public static function only(array $src, array $keys): array {
    $o = [];
    foreach ($keys as $k) { if (isset($src[$k])) $o[$k] = $src[$k]; }
    return $o;
  }
}
