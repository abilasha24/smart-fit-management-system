<?php
// backend/get_notifications.php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

$role = strtolower(trim($_SESSION["role"] ?? ""));
$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($user_id <= 0 || !in_array($role, ["member","trainer","admin"])) {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

// Optional query: ?limit=20
$limit = (int)($_GET["limit"] ?? 50);
if ($limit <= 0) $limit = 50;
if ($limit > 200) $limit = 200;

$sql = "
  SELECT id, title, message, is_read, created_at
  FROM notifications
  WHERE user_id=?
  ORDER BY created_at DESC, id DESC
  LIMIT ?
";

$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]);
  exit;
}

$stmt->bind_param("ii", $user_id, $limit);
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