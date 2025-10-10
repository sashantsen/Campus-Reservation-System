<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../../backend/core/Auth.php'; // adjust path if needed

Auth::logout(); // ✅ uses shared cookie and session

header('Location: ' . $BASE_URL . '/pages/home.php');
exit;
