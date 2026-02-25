<?php
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * ✅ Change this if your session key differs
 * Try these in order:
 */
$user_id = 0;
if (isset($_SESSION["user_id"])) $user_id = intval($_SESSION["user_id"]);
elseif (isset($_SESSION["id"])) $user_id = intval($_SESSION["id"]);
elseif (isset($_SESSION["user"]["id"])) $user_id = intval($_SESSION["user"]["id"]);

if (!$user_id) {
  echo json_encode(["ok" => false, "message" => "Not logged in", "session_keys" => array_keys($_SESSION)]);
  exit;
}

$sql = "
SELECT
  uw.id AS user_workout_id,
  uw.status,
  uw.assigned_at,
  uw.started_at,
  uw.completed_at,
  w.id AS workout_id,
  w.title,
  w.level,
  w.duration_min,
  w.calories,
  w.youtube_url
FROM user_workouts uw
JOIN workouts w ON w.id = uw.workout_id
WHERE uw.user_id = ?
ORDER BY uw.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) $items[] = $row;

echo json_encode(["ok" => true, "items" => $items]);
exit;