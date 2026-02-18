<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

session_start();
require_once "db.php";

$user_id = intval($_SESSION["user_id"] ?? 0);
if ($user_id <= 0) {
  echo json_encode(["ok"=>false, "message"=>"Not logged in"]);
  exit;
}

$sql = "
SELECT mw.workout_date, mw.status, mw.duration_min,
       w.title, w.level, w.duration_min as plan_duration, w.calories
FROM member_workouts mw
JOIN workouts w ON w.id = mw.workout_id
WHERE mw.user_id = ?
ORDER BY mw.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
  // myworkout.html expects duration_min for display
  $r["duration_min"] = $r["duration_min"] > 0 ? $r["duration_min"] : $r["plan_duration"];
  $rows[] = $r;
}

echo json_encode(["ok"=>true, "workouts"=>$rows]);
