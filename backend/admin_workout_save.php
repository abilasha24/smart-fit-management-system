<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents("php://input"), true);
$id = trim($input['id'] ?? '');
$title = trim($input['title'] ?? '');
$level = trim($input['level'] ?? 'beginner');
$duration_min = (int)($input['duration_min'] ?? 0);
$calories = (int)($input['calories'] ?? 0);

if ($title === '' || $duration_min <= 0) {
  echo json_encode(["ok"=>false,"message"=>"Title and Duration required"]);
  exit;
}

if ($id === '' || $id === '0') {
  // INSERT
  $stmt = $conn->prepare("INSERT INTO workouts (title, level, duration_min, calories, created_at) VALUES (?,?,?,?, NOW())");
  $stmt->bind_param("ssii", $title, $level, $duration_min, $calories);
  $ok = $stmt->execute();
  echo json_encode(["ok"=>$ok, "id"=>$conn->insert_id]);
  exit;
} else {
  // UPDATE
  $stmt = $conn->prepare("UPDATE workouts SET title=?, level=?, duration_min=?, calories=? WHERE id=?");
  $stmt->bind_param("ssiii", $title, $level, $duration_min, $calories, $id);
  $ok = $stmt->execute();
  echo json_encode(["ok"=>$ok]);
  exit;
}
