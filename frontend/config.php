<?php
// -------- Base paths (adjust only $ROOT if your folder name changes) --------
$ROOT = '/campus-study-room-reservation'; // project root under XAMPP htdocs

// Detect origin (http://localhost)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$origin = $scheme . '://' . $host;

// Frontend + assets
$BASE_URL   = $ROOT . '/frontend';
$ASSETS_URL = $BASE_URL . '/assets';

// Backend API base (public/index.php routes to /api)
$BASE_API   = $ROOT . '/backend/public/api';

// If you prefer absolute URLs (including host), uncomment these 3 lines:
// $BASE_URL   = $origin . $BASE_URL;
// $ASSETS_URL = $origin . $ASSETS_URL;
// $BASE_API   = $origin . $BASE_API;

// Export to global scope
$GLOBALS['BASE_URL']   = $BASE_URL;
$GLOBALS['ASSETS_URL'] = $ASSETS_URL;
$GLOBALS['BASE_API']   = $BASE_API;
