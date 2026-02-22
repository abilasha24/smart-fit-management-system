<?php
session_start();
header('Content-Type: application/json');

$role = $_SESSION['role'] ?? '';

if (empty($_SESSION['logged_in']) || !in_array($role, ['admin','trainer'])) {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$res = $conn->query("SELECT id, title, level, duration_min, calories, created_at FROM workouts ORDER BY id DESC");
$rows = [];
while($row = $res->fetch_assoc()){
  $rows[] = $row;
}
echo json_encode(["ok"=>true,"workouts"=>$rows]);
