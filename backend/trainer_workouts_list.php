<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$res = $conn->query("SELECT id, title, level, duration_min, calories, youtube_url, created_at FROM workouts ORDER BY id DESC");
if(!$res){
  echo json_encode(["ok"=>false,"message"=>"DB error: ".$conn->error]);
  exit;
}

$rows = [];
while($row = $res->fetch_assoc()){
  $rows[] = $row;
}

echo json_encode(["ok"=>true,"workouts"=>$rows]);