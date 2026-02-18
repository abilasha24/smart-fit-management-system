<?php
// backend/get_member_stats.php
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

// 1) workouts counts
$sql1 = "SELECT status, COUNT(*) AS cnt FROM member_workouts WHERE user_id=? GROUP BY status";
$stmt1 = $conn->prepare($sql1);
if(!$stmt1){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error]); exit;
}
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$res1 = $stmt1->get_result();

$started = 0; $completed = 0;
while($r = $res1->fetch_assoc()){
  if($r["status"] === "started") $started = (int)$r["cnt"];
  if($r["status"] === "completed") $completed = (int)$r["cnt"];
}

// 2) total calories + minutes (completed)
$sql2 = "
SELECT
  SUM(COALESCE(mw.duration_min, w.duration_min, 0)) AS total_minutes,
  SUM(COALESCE(w.calories, 0)) AS total_calories
FROM member_workouts mw
JOIN workouts w ON mw.workout_id = w.id
WHERE mw.user_id=? AND mw.status='completed'
";
$stmt2 = $conn->prepare($sql2);
if(!$stmt2){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error]); exit;
}
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$row2 = $stmt2->get_result()->fetch_assoc();
$total_minutes = (int)($row2["total_minutes"] ?? 0);
$total_calories = (int)($row2["total_calories"] ?? 0);

// 3) latest subscription/payment
$sql3 = "
SELECT plan, billing_cycle, amount, payment_method, created_at
FROM payments
WHERE user_id=?
ORDER BY created_at DESC
LIMIT 1
";
$stmt3 = $conn->prepare($sql3);
if(!$stmt3){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error]); exit;
}
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$sub = $stmt3->get_result()->fetch_assoc(); // can be null

// 4) unread notifications
$unread = 0;
$hasNotificationsTable = true;
$stmt4 = $conn->prepare("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND is_read=0");
if(!$stmt4){
  // if notifications table not created yet -> ignore
  $hasNotificationsTable = false;
} else {
  $stmt4->bind_param("i", $user_id);
  $stmt4->execute();
  $unread = (int)($stmt4->get_result()->fetch_assoc()["c"] ?? 0);
}

echo json_encode([
  "ok"=>true,
  "stats"=>[
    "started"=>$started,
    "completed"=>$completed,
    "total_minutes"=>$total_minutes,
    "total_calories"=>$total_calories,
    "unread_notifications"=>$unread,
    "has_notifications_table"=>$hasNotificationsTable
  ],
  "subscription"=>$sub
]);
