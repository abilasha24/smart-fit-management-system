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

$limit = (int)($_GET["limit"] ?? 50);
if ($limit <= 0 || $limit > 200) $limit = 50;

$stmt = $conn->prepare("
  SELECT id, user_id, title, message, is_read, created_at
  FROM notifications
  WHERE user_id = ?
  ORDER BY id DESC
  LIMIT ?
");
if(!$stmt){
  echo json_encode(["ok"=>false,"message"=>"Prepare failed","error"=>$conn->error]);
  exit;
}

$stmt->bind_param("ii", $user_id, $limit);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()){
  $row["is_read"] = (int)$row["is_read"];
  $items[] = $row;
}

$un = $conn->prepare("SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0");
$un->bind_param("i", $user_id);
$un->execute();
$unres = $un->get_result()->fetch_assoc();

echo json_encode([
  "ok"=>true,
  "unread"=>(int)($unres["c"] ?? 0),
  "items"=>$items
]);
exit;