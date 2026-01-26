<?php
// backend/check_auth.php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in'])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}

echo json_encode([
  "ok" => true,
  "role" => $_SESSION['role'] ?? ''
]);
