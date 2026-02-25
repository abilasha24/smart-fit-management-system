<?php
session_start();
header('Content-Type: application/json');

// 1️⃣ Auth check
if (!isset($_SESSION['role'])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}

if ($_SESSION['role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok" => false, "message" => "Forbidden"]);
  exit;
}

require_once "db.php";

// 2️⃣ Safe query (explicit columns)
$sql = "SELECT id, title, level, duration_min, calories, youtube_url, created_at
        FROM workouts
        ORDER BY id DESC";

$res = $conn->query($sql);

if (!$res) {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Database error"]);
  exit;
}

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode([
  "ok" => true,
  "count" => count($data),
  "workouts" => $data
]);

$conn->close();