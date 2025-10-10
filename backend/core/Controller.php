<?php
abstract class Controller {

  /**
   * Load a view file (optional if you use API only)
   */
  protected function view(string $name, array $data = []) {
    extract($data);
    require __DIR__ . '/../views/' . $name . '.php';
  }

  /**
   * Return JSON response and stop execution
   */
  protected function json($data, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

  /**
   * Get input data safely from JSON or form submissions
   */
  protected function input(array $keys, bool $required = true) {
    // --- Detect if incoming request is JSON ---
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    $isJson = stripos($contentType, 'application/json') !== false;

    if ($isJson) {
      // Parse JSON body
      $raw = file_get_contents('php://input');
      $data = json_decode($raw, true);
      if (!is_array($data)) {
        $data = [];
      }
    } else {
      // Handle normal form submissions
      $data = ($_SERVER['REQUEST_METHOD'] === 'GET') ? $_GET : $_POST;
    }

    // --- Validate required fields ---
    $out = [];
    foreach ($keys as $k) {
      if (!array_key_exists($k, $data) || $data[$k] === '') {
        if ($required) {
          $this->json(['error' => "Missing field: $k"], 422);
        }
        $out[$k] = null;
      } else {
        $out[$k] = is_string($data[$k]) ? trim($data[$k]) : $data[$k];
      }
    }

    return $out;
  }
}
