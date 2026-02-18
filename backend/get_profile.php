<?php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

$role = strtolower(trim($_SESSION["role"] ?? ""));
$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($user_id <= 0 || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=? LIMIT 1");
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Prepare failed"]);
  exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

echo json_encode(["ok"=>true, "user"=>$user]);
exit;
