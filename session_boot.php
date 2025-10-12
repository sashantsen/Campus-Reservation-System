<?php
/**
 * ----------------------------------------------------------
 * Shared Session Bootstrap
 * ----------------------------------------------------------
 * Ensures both BACKEND and FRONTEND share the same PHP session.
 * Include this in:
 *   - backend/core/Auth.php
 *   - frontend/layouts/header.php
 *   - backend/public/index.php (optional)
 */

if (session_status() === PHP_SESSION_ACTIVE) return;

// --- Give a consistent session name (important!) ---
session_name('campus_rooms');

// --- Define a cookie path that works for both frontend & backend ---
$cookiePath = '/campus-study-room-reservation';

// --- Secure cookie parameters ---
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => $cookiePath,
  'domain'   => '',       // keep blank for localhost
  'secure'   => false,    // true if HTTPS
  'httponly' => true,
  'samesite' => 'Lax'
]);

session_start();
