<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

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

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);
$workout_id = intval($body["workout_id"] ?? 0);

if ($workout_id <= 0) {
  echo json_encode(["ok"=>false, "message"=>"Missing workout_id"]);
  exit;
}

/* ✅ check already exists */
$check = $conn->prepare("SELECT id, status FROM member_workouts WHERE user_id=? AND workout_id=? LIMIT 1");
$check->bind_param("ii", $user_id, $workout_id);
$check->execute();
$res = $check->get_result();

if ($res && $res->num_rows > 0) {
  // already exists -> update to started
  $row = $res->fetch_assoc();
  $upd = $conn->prepare("UPDATE member_workouts SET status='started', workout_date=CURDATE() WHERE id=?");
  $upd->bind_param("i", $row["id"]);
  $upd->execute();

  echo json_encode(["ok"=>true, "message"=>"Workout started ✅"]);
  exit;
}

/* ✅ insert new */
$stmt = $conn->prepare("
  INSERT INTO member_workouts (user_id, workout_id, workout_date, status, duration_min)
  VALUES (?, ?, CURDATE(), 'started', 0)
");
$stmt->bind_param("ii", $user_id, $workout_id);

if ($stmt->execute()) {
  echo json_encode(["ok"=>true, "message"=>"Workout started ✅"]);
} else {
  echo json_encode(["ok"=>false, "message"=>"Insert failed: ".$stmt->error]);
}
