<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? '';
$id = (int)$id;
if ($id <= 0) {
  echo json_encode(["ok"=>false,"message"=>"Invalid id"]);
  exit;
}

$stmt = $conn->prepare("DELETE FROM workouts WHERE id=?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();

echo json_encode(["ok"=>$ok]);
