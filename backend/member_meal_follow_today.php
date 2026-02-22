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
if($user_id <= 0){
  echo json_encode(["ok"=>false,"message"=>"Invalid session"]);
  exit;
}

$sql = "INSERT IGNORE INTO meal_follow_log (user_id, log_date) VALUES (?, CURDATE())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if(!$stmt->execute()){
  echo json_encode(["ok"=>false,"message"=>"DB error: ".$stmt->error]);
  exit;
}

echo json_encode(["ok"=>true]);