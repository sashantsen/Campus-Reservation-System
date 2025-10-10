<?php
class Route {
  private static array $routes = ['GET'=>[], 'POST'=>[]];

  public static function get(string $path, array $action)  { self::$routes['GET'][$path]  = $action; }
  public static function post(string $path, array $action) { self::$routes['POST'][$path] = $action; }

  public static function dispatch() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // adjust base if project is in a subfolder (public/)
    // e.g. /campus-study-room-reservation/backend/public
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if ($base && str_starts_with($uri, $base)) {
      $uri = substr($uri, strlen($base));
    }
    if ($uri === '' || $uri === false) $uri = '/';

    $routes = self::$routes[$method] ?? [];
    if (!array_key_exists($uri, $routes)) {
      http_response_code(404);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Route not found', 'path' => $uri]);
      exit;
    }
    [$class, $methodName] = $routes[$uri];
    $controller = new $class();
    return $controller->$methodName();
  }
}

class App {
  public function run() {
    Route::dispatch();
  }
}
