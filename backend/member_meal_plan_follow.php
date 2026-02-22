<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'member') {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

require_once __DIR__ . "/db.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$plan_id = (int)($data["plan_id"] ?? 0);
$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($plan_id <= 0 || $user_id <= 0) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid plan_id"]);
  exit;
}

$today = date("Y-m-d");

// Insert (ignore duplicates for same day)
$sql = "INSERT INTO meal_plan_logs (user_id, plan_id, log_date)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE created_at = created_at";
$stmt = $conn->prepare($sql);

if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed: ".$conn->error]);
  exit;
}

$stmt->bind_param("iis", $user_id, $plan_id, $today);
$ok = $stmt->execute();

if(!$ok){
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"SQL execute failed: ".$stmt->error]);
  exit;
}

echo json_encode(["ok"=>true, "message"=>"Marked as followed today", "date"=>$today]);