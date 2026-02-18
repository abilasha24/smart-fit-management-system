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

// 1) status counts (started / completed)
$sql1 = "SELECT status, COUNT(*) AS cnt
         FROM user_workouts
         WHERE user_id=?
         GROUP BY status";

$stmt1 = $conn->prepare($sql1);
if(!$stmt1){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed (statusCounts)","error"=>$conn->error]);
  exit;
}
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$res1 = $stmt1->get_result();

$statusCounts = ["started"=>0, "completed"=>0];
while($r = $res1->fetch_assoc()){
  $s = strtolower($r["status"] ?? "");
  if (isset($statusCounts[$s])) {
    $statusCounts[$s] = (int)$r["cnt"];
  }
}

// 2) per-day totals (completed only)
// user_workouts table has: created_at, completed_at
// pick date from completed_at if available else created_at
$sql2 = "
SELECT
  DATE(COALESCE(uw.completed_at, uw.created_at)) AS d,
  SUM(COALESCE(w.duration_min,0)) AS total_minutes,
  SUM(COALESCE(w.calories,0)) AS total_calories,
  COUNT(*) AS completed_count
FROM user_workouts uw
JOIN workouts w ON uw.workout_id = w.id
WHERE uw.user_id=? AND uw.status='completed'
GROUP BY d
ORDER BY d ASC
";

$stmt2 = $conn->prepare($sql2);
if(!$stmt2){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed (daily)","error"=>$conn->error]);
  exit;
}
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$daily = [];
while($row = $res2->fetch_assoc()){
  $daily[] = [
    "date" => $row["d"],
    "minutes" => (int)($row["total_minutes"] ?? 0),
    "calories" => (int)($row["total_calories"] ?? 0),
    "completed_count" => (int)($row["completed_count"] ?? 0)
  ];
}

echo json_encode([
  "ok"=>true,
  "statusCounts"=>$statusCounts,
  "daily"=>$daily
]);
