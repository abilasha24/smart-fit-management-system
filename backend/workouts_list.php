<?php
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

try {
  $sql = "SELECT id, title, level, duration_min, calories, youtube_url
          FROM workouts
          ORDER BY id DESC";
  $res = $conn->query($sql);

  $items = [];
  while ($row = $res->fetch_assoc()) {
    $items[] = $row;
  }

  echo json_encode(["ok" => true, "workouts" => $items]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Server error"]);
}