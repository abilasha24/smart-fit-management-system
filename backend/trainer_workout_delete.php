<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$id = (int)($_GET["id"] ?? 0);
if($id <= 0){
  echo json_encode(["ok"=>false,"message"=>"Invalid id"]);
  exit;
}

$stmt = $conn->prepare("DELETE FROM workouts WHERE id=?");
if(!$stmt){ echo json_encode(["ok"=>false,"message"=>"SQL prepare failed: ".$conn->error]); exit; }
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
if(!$ok){ echo json_encode(["ok"=>false,"message"=>"Delete failed: ".$stmt->error]); exit; }

echo json_encode(["ok"=>true]);