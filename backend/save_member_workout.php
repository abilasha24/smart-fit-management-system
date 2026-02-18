<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$workout_id = (int)($data["workout_id"] ?? 0);
$duration_min = (int)($data["duration_min"] ?? 0);
$status = trim($data["status"] ?? "started");

if ($workout_id <= 0) {
  http_response_code(400);
  echo json_encode(["ok" => false, "message" => "workout_id required"]);
  exit;
}

$user_id = (int)$_SESSION["user_id"];
$workout_date = date("Y-m-d");

$stmt = $conn->prepare("
  INSERT INTO member_workouts (user_id, workout_id, workout_date, status, duration_min)
  VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iissi", $user_id, $workout_id, $workout_date, $status, $duration_min);

if ($stmt->execute()) {
  echo json_encode(["ok" => true, "message" => "Workout saved!"]);
} else {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "DB insert failed"]);
}
exit;
