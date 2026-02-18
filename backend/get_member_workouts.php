<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

$user_id = isset($_GET["user_id"]) ? intval($_GET["user_id"]) : 0;
if ($user_id <= 0) {
  echo json_encode(["status"=>"error","message"=>"Missing user_id"]);
  exit;
}

$sql = "
SELECT mw.id as member_workout_id, mw.workout_date, mw.status, mw.duration_min,
       w.id as workout_id, w.title, w.level, w.duration_min as plan_duration, w.calories
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
while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(["status"=>"success","items"=>$rows]);
