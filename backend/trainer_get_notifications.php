<?php
// backend/trainer_get_notifications.php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

$role = strtolower(trim($_SESSION["role"] ?? ""));
$user_id = (int)($_SESSION["user_id"] ?? 0);

if($user_id <= 0 || $role !== "trainer"){
  respond(["ok"=>false,"message"=>"Login required"], 401);
}

$mode = strtolower(trim($_GET["mode"] ?? "")); // "count" optional

// unread count
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND is_read=0");
if(!$stmt) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$unread = (int)($row["c"] ?? 0);

if($mode === "count"){
  respond(["ok"=>true,"unread"=>$unread]);
}

// list (latest 50)
$stmt2 = $conn->prepare("
  SELECT id, title, message, is_read, created_at
  FROM notifications
  WHERE user_id=?
  ORDER BY created_at DESC, id DESC
  LIMIT 50
");
if(!$stmt2) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$res = $stmt2->get_result();

$list = [];
while($r = $res->fetch_assoc()){
  $r["is_read"] = (int)$r["is_read"];
  $list[] = $r;
}

respond([
  "ok"=>true,
  "unread"=>$unread,
  "notifications"=>$list
]);