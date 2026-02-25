<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/db.php";

$role = strtolower(trim($_SESSION["role"] ?? ""));
$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($user_id <= 0 || $role !== "trainer") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Trainer login required"]);
  exit;
}

$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode(["ok"=>true]);
exit;