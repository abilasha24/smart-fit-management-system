<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'member') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);

$today = 0;
$last7 = 0;

$stmt = $conn->prepare("SELECT COUNT(*) c FROM meal_follow_log WHERE user_id=? AND log_date=CURDATE()");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$stmt->bind_result($today);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) c FROM meal_follow_log WHERE user_id=? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$stmt->bind_result($last7);
$stmt->fetch();
$stmt->close();

echo json_encode([
  "ok"=>true,
  "followed_today"=>($today > 0),
  "last7" => (int)$last7
]);