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
if ($user_id <= 0) {
  http_response_code(401);
  echo json_encode(["ok"=>false,"message"=>"Not logged in"]);
  exit;
}

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);
$plan_id = isset($body["plan_id"]) ? (int)$body["plan_id"] : null;

// Insert / update today log
$sql = "INSERT INTO meal_plan_logs (user_id, plan_id, log_date)
        VALUES (?, ?, CURDATE())
        ON DUPLICATE KEY UPDATE plan_id = VALUES(plan_id)";
$stmt = $conn->prepare($sql);
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
  exit;
}
$stmt->bind_param("ii", $user_id, $plan_id);
$stmt->execute();

// return updated stats
$st = $conn->prepare("
  SELECT
    (SELECT COUNT(*) FROM meal_plan_logs WHERE user_id=? AND log_date=CURDATE()) AS today,
    (SELECT COUNT(DISTINCT log_date) FROM meal_plan_logs
      WHERE user_id=? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ) AS last7
");
$st->bind_param("ii", $user_id, $user_id);
$st->execute();
$res = $st->get_result()->fetch_assoc();

echo json_encode([
  "ok" => true,
  "followed_today" => ((int)$res["today"] > 0),
  "last7_days" => (int)$res["last7"]
]);