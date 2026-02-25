<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/db.php";

error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) session_start();

/** ✅ detect member user_id from session */
$user_id = 0;
if (isset($_SESSION["user_id"])) $user_id = (int)$_SESSION["user_id"];
elseif (isset($_SESSION["id"])) $user_id = (int)$_SESSION["id"];
elseif (isset($_SESSION["user"]["id"])) $user_id = (int)$_SESSION["user"]["id"];

$input  = json_decode(file_get_contents("php://input"), true);
$uw_id  = (int)($input["user_workout_id"] ?? 0);
$status = strtolower(trim($input["status"] ?? ""));

if (!$user_id) {
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}
if (!$uw_id) {
  echo json_encode(["ok" => false, "message" => "Missing user_workout_id"]);
  exit;
}

function safeFetchAssoc($stmt){
  $res = $stmt->get_result();
  if(!$res) return null;
  return $res->fetch_assoc();
}

/* ---------- STARTED ---------- */
if ($status === "started") {
  $stmt = $conn->prepare("
    UPDATE user_workouts
    SET status='started', started_at=NOW()
    WHERE id=? AND user_id=?
  ");
  $stmt->bind_param("ii", $uw_id, $user_id);
  $ok = $stmt->execute();

  echo json_encode(["ok" => (bool)$ok]);
  exit;
}

/* ---------- COMPLETED ---------- */
if ($status === "completed") {

  // 1) Update workout as completed
  $stmt = $conn->prepare("
    UPDATE user_workouts
    SET status='completed', completed_at=NOW()
    WHERE id=? AND user_id=?
  ");
  $stmt->bind_param("ii", $uw_id, $user_id);
  $ok = $stmt->execute();

  if (!$ok) {
    echo json_encode(["ok" => false, "message" => "Update failed"]);
    exit;
  }

  // 2) ✅ After success: notify trainer (silent fail if any error)
  try {
    // Fetch trainer_id + workout title + member name/email
    $q = $conn->prepare("
      SELECT
        uw.trainer_id,
        w.title AS workout_title,
        u.name  AS member_name,
        u.email AS member_email
      FROM user_workouts uw
      JOIN workouts w ON w.id = uw.workout_id
      JOIN users u    ON u.id = uw.user_id
      WHERE uw.id=? AND uw.user_id=?
      LIMIT 1
    ");
    $q->bind_param("ii", $uw_id, $user_id);
    if ($q->execute()) {
      $row = safeFetchAssoc($q);

      $trainer_id = (int)($row["trainer_id"] ?? 0);
      if ($trainer_id > 0) {
        $workoutTitle = trim($row["workout_title"] ?? "Workout");
        $memberName   = trim($row["member_name"] ?? "A member");
        $memberEmail  = trim($row["member_email"] ?? "");

        $title = "Workout completed";
        $msg   = $memberEmail !== ""
          ? "{$memberName} ({$memberEmail}) completed: {$workoutTitle}"
          : "{$memberName} completed: {$workoutTitle}";

        $ins = $conn->prepare("
          INSERT INTO notifications (user_id, title, message, is_read, created_at)
          VALUES (?, ?, ?, 0, NOW())
        ");
        $ins->bind_param("iss", $trainer_id, $title, $msg);
        $ins->execute();
      }
    }
  } catch (Throwable $e) {
    // ignore notification errors
  }

  echo json_encode(["ok" => true]);
  exit;
}

echo json_encode(["ok" => false, "message" => "Invalid status"]);
exit;