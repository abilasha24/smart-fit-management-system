<?php
session_start();
header("Content-Type: application/json");

if (empty($_SESSION["logged_in"]) || ($_SESSION["role"] ?? "") !== "trainer") {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

$trainer_id = (int)($_SESSION["user_id"] ?? 0);
$user_id    = (int)($_POST["user_id"] ?? 0);      // member id
$workout_id = (int)($_POST["workout_id"] ?? 0);

if ($trainer_id<=0 || $user_id<=0 || $workout_id<=0) {
  echo json_encode(["ok"=>false,"message"=>"user_id and workout_id required"]);
  exit;
}

/*
  Insert assignment row:
  - status = assigned
  - assigned_at = NOW()
  - trainer_id saved (important for stats & recent list)
*/
$stmt = $conn->prepare("
  INSERT INTO user_workouts (user_id, trainer_id, workout_id, status, assigned_at, created_at)
  VALUES (?, ?, ?, 'assigned', NOW(), NOW())
");
if(!$stmt){
  echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
  exit;
}
$stmt->bind_param("iii", $user_id, $trainer_id, $workout_id);

$ok = $stmt->execute();
$stmt->close();

if(!$ok){
  echo json_encode(["ok"=>false,"message"=>"Assign failed"]);
  exit;
}

echo json_encode(["ok"=>true,"message"=>"Workout assigned"]);