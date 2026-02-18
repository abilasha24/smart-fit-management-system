<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

try {
  $q = $conn->query("SELECT id, title, level, duration_min, calories FROM workouts ORDER BY id DESC");
  $rows = [];
  if ($q) {
    while ($r = $q->fetch_assoc()) $rows[] = $r;
  }
  echo json_encode(["ok" => true, "workouts" => $rows]);
} catch (Throwable $e) {
  echo json_encode(["ok" => false, "message" => "Server error: " . $e->getMessage()]);
}
