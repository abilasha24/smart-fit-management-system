<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in'])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}

echo json_encode([
  "ok" => true,
  "username" => $_SESSION['username'] ?? 'User',
  "email" => $_SESSION['email'] ?? '',
  "role" => $_SESSION['role'] ?? ''
]);
exit;
