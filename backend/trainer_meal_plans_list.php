<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

$stmt = $conn->prepare("SELECT id,title,content,created_at FROM meal_plans WHERE trainer_id=? ORDER BY id DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();

$plans=[];
while($row=$res->fetch_assoc()){
  $plans[]=$row;
}

echo json_encode(["ok"=>true,"plans"=>$plans]);