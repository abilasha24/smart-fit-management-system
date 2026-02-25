<?php
session_start();
header('Content-Type: application/json');

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

$title   = trim($_POST['title'] ?? '');
$level   = trim($_POST['level'] ?? '');
$duration = intval($_POST['duration'] ?? 0);
$calories = intval($_POST['calories'] ?? 0);
$youtube  = trim($_POST['youtube'] ?? '');

// validations
$allowed_levels = ['beginner', 'intermediate', 'advanced'];

if ($title === '') {
  http_response_code(422);
  echo json_encode(["ok" => false, "message" => "Title is required"]);
  exit;
}
if (!in_array($level, $allowed_levels, true)) {
  http_response_code(422);
  echo json_encode(["ok" => false, "message" => "Invalid level"]);
  exit;
}
if ($duration <= 0) {
  http_response_code(422);
  echo json_encode(["ok" => false, "message" => "Duration must be > 0"]);
  exit;
}
if ($calories < 0) {
  http_response_code(422);
  echo json_encode(["ok" => false, "message" => "Calories must be >= 0"]);
  exit;
}

// youtube optional -> store NULL
$youtube_param = ($youtube === '') ? null : $youtube;

$stmt = $conn->prepare(
  "INSERT INTO workouts (title, level, duration_min, calories, youtube_url, created_at)
   VALUES (?, ?, ?, ?, ?, NOW())"
);

if (!$stmt) {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Prepare failed"]);
  exit;
}

$stmt->bind_param("ssiis", $title, $level, $duration, $calories, $youtube_param);

if ($stmt->execute()) {
  echo json_encode(["ok" => true, "message" => "Workout added successfully"]);
} else {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Insert failed"]);
}

$stmt->close();
$conn->close();