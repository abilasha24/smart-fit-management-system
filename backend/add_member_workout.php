<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["status"=>"error","message"=>"Invalid request"]);
  exit;
}

$user_id    = isset($_POST["user_id"]) ? intval($_POST["user_id"]) : 0;
$workout_id = isset($_POST["workout_id"]) ? intval($_POST["workout_id"]) : 0;

if ($user_id <= 0 || $workout_id <= 0) {
  echo json_encode(["status"=>"error","message"=>"Missing user_id or workout_id"]);
  exit;
}

// ✅ prevent duplicate
$check = $conn->prepare("SELECT id FROM member_workouts WHERE user_id=? AND workout_id=? LIMIT 1");
$check->bind_param("ii", $user_id, $workout_id);
$check->execute();
$res = $check->get_result();

if ($res && $res->num_rows > 0) {
  echo json_encode(["status"=>"error","message"=>"Workout already added"]);
  exit;
}

// ✅ insert
$stmt = $conn->prepare("
  INSERT INTO member_workouts (user_id, workout_id, workout_date, status, duration_min)
  VALUES (?, ?, CURDATE(), 'selected', 0)
");
$stmt->bind_param("ii", $user_id, $workout_id);

if ($stmt->execute()) {
  echo json_encode(["status"=>"success","message"=>"Workout added to My Workouts"]);
} else {
  echo json_encode(["status"=>"error","message"=>"Insert failed: ".$stmt->error]);
}
