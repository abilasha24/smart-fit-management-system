<?php
// backend/get_progress.php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

// member auth
$role = strtolower(trim($_SESSION["role"] ?? ""));
if (!isset($_SESSION["user_id"]) || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}
$user_id = (int)$_SESSION["user_id"];

// 1) status counts
$sql1 = "SELECT status, COUNT(*) AS cnt FROM member_workouts WHERE user_id=? GROUP BY status";
$stmt1 = $conn->prepare($sql1);
if(!$stmt1){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error]); exit;
}
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$res1 = $stmt1->get_result();

$statusCounts = ["started"=>0, "completed"=>0];
while($r = $res1->fetch_assoc()){
  $statusCounts[$r["status"]] = (int)$r["cnt"];
}

// 2) per-day totals (completed only)
$sql2 = "
SELECT
  mw.workout_date AS d,
  SUM(COALESCE(mw.duration_min, w.duration_min, 0)) AS total_minutes,
  SUM(COALESCE(w.calories,0)) AS total_calories,
  COUNT(*) AS completed_count
FROM member_workouts mw
JOIN workouts w ON mw.workout_id = w.id
WHERE mw.user_id=? AND mw.status='completed'
GROUP BY mw.workout_date
ORDER BY mw.workout_date ASC
";
$stmt2 = $conn->prepare($sql2);
if(!$stmt2){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error]); exit;
}
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$daily = [];
while($row = $res2->fetch_assoc()){
  $daily[] = [
    "date" => $row["d"],
    "minutes" => (int)$row["total_minutes"],
    "calories" => (int)$row["total_calories"],
    "completed_count" => (int)$row["completed_count"]
  ];
}

echo json_encode([
  "ok"=>true,
  "statusCounts"=>$statusCounts,
  "daily"=>$daily
]);
