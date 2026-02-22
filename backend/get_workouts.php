<?php
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

try {
  $q = $conn->query("SELECT id, title, level, duration_min, calories, COALESCE(youtube_url,'') AS youtube_url
                     FROM workouts
                     ORDER BY id DESC");
  $rows = [];
  if ($q) {
    while ($r = $q->fetch_assoc()) $rows[] = $r;
  }
  echo json_encode(["ok" => true, "workouts" => $rows]);
} catch (Throwable $e) {
  echo json_encode(["ok" => false, "message" => "Server error: " . $e->getMessage()]);
}