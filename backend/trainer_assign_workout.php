<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/db.php";
error_reporting(0); ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
  echo json_encode(["ok"=>false,"message"=>"Not logged in"]);
  exit;
}
if (strtolower($_SESSION["role"] ?? "") !== "trainer") {
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

$trainer_id = (int)$_SESSION["user_id"];

$input = json_decode(file_get_contents("php://input"), true);
$user_id    = (int)($input["user_id"] ?? 0);     // member id
$workout_id = (int)($input["workout_id"] ?? 0);

// ✅ status default assigned
$status = trim($input["status"] ?? "assigned");
if ($status === "") $status = "assigned";
$status = strtolower($status);

if (!$user_id || !$workout_id) {
  echo json_encode(["ok"=>false,"message"=>"Missing user_id/workout_id"]);
  exit;
}
if (!in_array($status, ["assigned","started","completed"], true)) $status = "assigned";

/* 1) Insert assignment */
$stmt = $conn->prepare("
  INSERT INTO user_workouts (user_id, trainer_id, workout_id, status, assigned_at, created_at)
  VALUES (?, ?, ?, ?, NOW(), NOW())
");
$stmt->bind_param("iiis", $user_id, $trainer_id, $workout_id, $status);

$ok = $stmt->execute();
if (!$ok) {
  echo json_encode(["ok"=>false,"message"=>"DB insert failed"]);
  exit;
}

$assigned_id = (int)$conn->insert_id;

/* 2) ✅ Insert notification to member */
try {
  // Get workout title for nice message
  $wtitle = "Workout";
  $ws = $conn->prepare("SELECT title FROM workouts WHERE id=? LIMIT 1");
  $ws->bind_param("i", $workout_id);
  if ($ws->execute()) {
    $wr = $ws->get_result()->fetch_assoc();
    if ($wr && !empty($wr["title"])) $wtitle = $wr["title"];
  }

  $title = "New workout assigned";
  $msg   = "Trainer assigned: {$wtitle}. Please start today.";

  $ns = $conn->prepare("
    INSERT INTO notifications (user_id, title, message, is_read, created_at)
    VALUES (?, ?, ?, 0, NOW())
  ");
  $ns->bind_param("iss", $user_id, $title, $msg);
  $ns->execute();
} catch (Throwable $e) {
  // notification fail ஆனாலும் assignment success ஆகட்டும் (silent fail)
}

echo json_encode(["ok"=>true, "id"=>$assigned_id]);
exit;