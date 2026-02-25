<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["ok"=>false, "message"=>"Method not allowed"]);
  exit;
}

require_once __DIR__ . "/db.php";

$body = json_decode(file_get_contents("php://input"), true);
$id = (int)($body["id"] ?? 0);
$status = trim($body["status"] ?? "");

if ($id <= 0 || !in_array($status, ["active","blocked"], true)) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid data"]);
  exit;
}

// ✅ prevent admin from blocking themselves
if ($id === (int)($_SESSION['user_id'] ?? 0)) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"You cannot change your own status"]);
  exit;
}

$stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Prepare failed"]);
  exit;
}

$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
  $stmt->close();
  echo json_encode(["ok"=>true, "id"=>$id, "status"=>$status]);
  exit;
}

$stmt->close();
http_response_code(500);
echo json_encode(["ok"=>false, "message"=>"DB error"]);
exit;