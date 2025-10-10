<?php
// config/config.php
$env = __DIR__ . '/.env';
if (file_exists($env)) {
  foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if ($line === '' || str_starts_with(trim($line), '#')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $_ENV[$k] = $v;
    putenv("$k=$v");
  }
}

define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_DEBUG', (($_ENV['APP_DEBUG'] ?? 'false') === 'true'));
define('APP_URL', $_ENV['APP_URL'] ?? '');

define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'campus_session');

if (APP_DEBUG) {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}
