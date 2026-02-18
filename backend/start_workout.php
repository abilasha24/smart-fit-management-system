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

/*
  ✅ Accept BOTH:
  1) FormData -> $_POST['workout_id']
  2) JSON body -> php://input
*/
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

/*
  ✅ Use correct table: user_workouts (not member_workouts)
  - if already started -> return ok
  - else insert started
*/
$check = $conn->prepare("
  SELECT id, status
  FROM user_workouts
  WHERE user_id=? AND workout_id=?
  ORDER BY id DESC
  LIMIT 1
");
$check->bind_param("ii", $user_id, $workout_id);
$check->execute();
$res = $check->get_result();

if ($res && $res->num_rows > 0) {
  $row = $res->fetch_assoc();
  if ($row["status"] === "started") {
    echo json_encode(["ok"=>true, "message"=>"Already started ✅"]);
    exit;
  }
}

/* insert as started */
$stmt = $conn->prepare("
  INSERT INTO user_workouts (user_id, workout_id, status)
  VALUES (?, ?, 'started')
");
$stmt->bind_param("ii", $user_id, $workout_id);

if ($stmt->execute()) {
  echo json_encode(["ok"=>true, "message"=>"Workout started ✅"]);
} else {
  echo json_encode(["ok"=>false, "message"=>"Insert failed: ".$stmt->error]);
}
