<?php
// backend/get_member_dashboard.php
session_start();
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");

// ✅ member auth
$role = strtolower(trim($_SESSION["role"] ?? ""));
if (!isset($_SESSION["user_id"]) || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}
$user_id = (int)$_SESSION["user_id"];

// ✅ status counts (started/completed)
$sql1 = "SELECT status, COUNT(*) AS cnt FROM member_workouts WHERE user_id=? GROUP BY status";
$stmt1 = $conn->prepare($sql1);
if(!$stmt1){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]); exit;
}
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$res1 = $stmt1->get_result();

$counts = ["started"=>0, "completed"=>0];
while($row = $res1->fetch_assoc()){
  $status = $row["status"];
  $counts[$status] = (int)$row["cnt"];
}

// ✅ totals (completed only)
$sql2 = "
SELECT
  SUM(COALESCE(mw.duration_min,0)) AS total_minutes,
  SUM(COALESCE(w.calories,0)) AS total_calories
FROM member_workouts mw
JOIN workouts w ON mw.workout_id = w.id
WHERE mw.user_id=? AND mw.status='completed'
";
$stmt2 = $conn->prepare($sql2);
if(!$stmt2){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]); exit;
}
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$tot = $stmt2->get_result()->fetch_assoc();

$total_minutes = (int)($tot["total_minutes"] ?? 0);
$total_calories = (int)($tot["total_calories"] ?? 0);

// ✅ recent workouts (last 6)
$sql3 = "
SELECT mw.id, w.title, mw.status, mw.duration_min, w.calories, mw.workout_date
FROM member_workouts mw
JOIN workouts w ON mw.workout_id = w.id
WHERE mw.user_id=?
ORDER BY mw.workout_date DESC, mw.id DESC
LIMIT 6
";
$stmt3 = $conn->prepare($sql3);
if(!$stmt3){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]); exit;
}
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$res3 = $stmt3->get_result();

$recent = [];
while($r = $res3->fetch_assoc()){
  $recent[] = $r;
}

// ✅ unread notifications (if table exists)
$unread = 0;
$check = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($check && $check->num_rows > 0) {
  $sql4 = "SELECT COUNT(*) AS unread FROM notifications WHERE user_id=? AND is_read=0";
  $stmt4 = $conn->prepare($sql4);
  if($stmt4){
    $stmt4->bind_param("i", $user_id);
    $stmt4->execute();
    $unread = (int)($stmt4->get_result()->fetch_assoc()["unread"] ?? 0);
  }
}

echo json_encode([
  "ok"=>true,
  "counts"=>$counts,
  "total_minutes"=>$total_minutes,
  "total_calories"=>$total_calories,
  "unread_notifications"=>$unread,
  "recent"=>$recent
]);
