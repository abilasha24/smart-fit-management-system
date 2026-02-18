<?php
// backend/get_notifications.php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

// member auth
$role = strtolower(trim($_SESSION["role"] ?? ""));
if (!isset($_SESSION["user_id"]) || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}
$user_id = (int)$_SESSION["user_id"];

// list notifications
$sql = "
SELECT id, title, message, is_read, created_at
FROM notifications
WHERE user_id=?
ORDER BY created_at DESC, id DESC
";
$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]);
  exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$list = [];
$unread = 0;

while($r = $res->fetch_assoc()){
  $r["is_read"] = (int)$r["is_read"];
  if($r["is_read"] === 0) $unread++;
  $list[] = $r;
}

echo json_encode([
  "ok"=>true,
  "unread"=>$unread,
  "notifications"=>$list
]);
