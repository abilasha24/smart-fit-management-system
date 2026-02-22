<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'member') {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

require_once __DIR__ . "/db.php";

$user_id = (int)($_SESSION["user_id"] ?? 0);
if ($user_id <= 0) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid user"]);
  exit;
}

$today = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 day"));

// Followed today?
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM meal_plan_logs WHERE user_id=? AND log_date=?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$todayCount = (int)($stmt->get_result()->fetch_assoc()["c"] ?? 0);

// Last 7 days count
$stmt7 = $conn->prepare("SELECT COUNT(DISTINCT log_date) AS c
                         FROM meal_plan_logs
                         WHERE user_id=? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
$stmt7->bind_param("i", $user_id);
$stmt7->execute();
$last7 = (int)($stmt7->get_result()->fetch_assoc()["c"] ?? 0);

// Streak (overall): consecutive days ending today (if no today, end at yesterday)
$endDate = $todayCount > 0 ? $today : $yesterday;

// Fetch last 60 days distinct dates
$stmtD = $conn->prepare("SELECT DISTINCT log_date
                         FROM meal_plan_logs
                         WHERE user_id=? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                         ORDER BY log_date DESC");
$stmtD->bind_param("i", $user_id);
$stmtD->execute();
$res = $stmtD->get_result();

$dates = [];
while($r = $res->fetch_assoc()){
  $dates[] = $r["log_date"];
}
$set = array_flip($dates);

$streak = 0;
$cursor = $endDate;

// count backwards consecutive
for($i=0; $i<60; $i++){
  if(isset($set[$cursor])){
    $streak++;
    $cursor = date("Y-m-d", strtotime($cursor." -1 day"));
  } else {
    break;
  }
}

echo json_encode([
  "ok"=>true,
  "followed_today" => $todayCount > 0,
  "last7_days" => $last7,
  "streak_days" => $streak
]);