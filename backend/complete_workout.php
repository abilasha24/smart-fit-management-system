<?php
header("Content-Type: application/json");
session_start();
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["ok"=>false, "message"=>"Invalid request"]);
  exit;
}

$user_id = intval($_SESSION["user_id"] ?? 0);
if ($user_id <= 0) {
  echo json_encode(["ok"=>false, "message"=>"Not logged in"]);
  exit;
}

$workout_id = 0;

if (isset($_POST["workout_id"])) {
  $workout_id = intval($_POST["workout_id"]);
} else {
  $raw = file_get_contents("php://input");
  $body = json_decode($raw, true);
  $workout_id = intval($body["workout_id"] ?? 0);
}

if ($workout_id <= 0) {
  echo json_encode(["ok"=>false, "message"=>"Missing workout_id"]);
  exit;
}

/* ✅ update latest started row to completed */
$upd = $conn->prepare("
  UPDATE user_workouts
  SET status='completed', completed_at=NOW()
  WHERE user_id=? AND workout_id=? AND status='started'
  ORDER BY id DESC
  LIMIT 1
");
$upd->bind_param("ii", $user_id, $workout_id);
$ok = $upd->execute();

if ($ok && $upd->affected_rows > 0) {
  echo json_encode(["ok"=>true, "message"=>"Workout completed ✅"]);
} else {
  echo json_encode(["ok"=>false, "message"=>"No started workout to complete"]);
}
