<?php
declare(strict_types=1);

/**
 * Front Controller
 * http://localhost/campus-study-room-reservation/backend/public/...
 */
$BASE = '/campus-study-room-reservation/backend/public';

// ---- Bootstrap ----
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/App.php';

spl_autoload_register(function($class){
  foreach ([
    __DIR__ . '/../controllers/' . $class . '.php',
    __DIR__ . '/../models/'      . $class . '.php',
    __DIR__ . '/../core/'        . $class . '.php',
  ] as $p) if (file_exists($p)) { require_once $p; return; }
});

Auth::start();
require_once __DIR__ . '/../routes/web.php';

// ---- Resolve logical path relative to $BASE ----
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = ltrim(substr($uri, strlen($BASE)), '/');
if ($path === false) $path = '';

// Fallback (works even if .htaccess is off)
if (!empty($_GET['route'])) $path = ltrim((string)$_GET['route'], '/');

// ---- Dispatch ----
$app = new App();
$app->run();
