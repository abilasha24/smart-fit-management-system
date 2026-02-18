<?php
header("Content-Type: application/json");
session_start();
require_once "db.php";

$user_id = intval($_SESSION["user_id"] ?? 0);
if ($user_id <= 0) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}

/*
  Tables used:
  - user_workouts (user_id, workout_id, status, created_at, completed_at)
  - workouts (id, title, level, duration_min, calories)
*/

$sql = "
  SELECT
    uw.id AS uw_id,
    uw.workout_id,
    uw.status,
    uw.created_at,
    uw.completed_at,
    w.title,
    w.level,
    w.duration_min,
    w.calories
  FROM user_workouts uw
  JOIN workouts w ON w.id = uw.workout_id
  WHERE uw.user_id = ?
  ORDER BY uw.id DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(["ok" => false, "message" => "SQL prepare failed: " . $conn->error]);
  exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
  // display date: completed_at இல்லைனா created_at
  $date = $r["completed_at"] ?: $r["created_at"];

  $rows[] = [
    "id" => intval($r["uw_id"]),
    "workout_id" => intval($r["workout_id"]),
    "title" => $r["title"],
    "level" => $r["level"],
    "duration_min" => intval($r["duration_min"]),
    "calories" => intval($r["calories"]),
    "status" => $r["status"],
    "date" => $date
  ];
}

echo json_encode(["ok" => true, "items" => $rows]);
