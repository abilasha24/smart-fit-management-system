<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$title   = trim($data["title"] ?? "");
$content = trim($data["content"] ?? "");

if ($title === "" || $content === "") {
  echo json_encode(["ok"=>false,"message"=>"Title and content required"]);
  exit;
}

$trainer_id = (int)$_SESSION["user_id"];

$stmt = $conn->prepare("INSERT INTO meal_plans (trainer_id, title, content) VALUES (?, ?, ?)");
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
  exit;
}

$stmt->bind_param("iss", $trainer_id, $title, $content);

if(!$stmt->execute()){
  http_response_code(500);
  echo json_encode(["ok"=>false,"message"=>"Insert failed: ".$stmt->error]);
  exit;
}

echo json_encode(["ok"=>true,"id"=>$stmt->insert_id]);