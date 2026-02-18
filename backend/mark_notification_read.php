<?php
// backend/mark_notification_read.php
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

if($_SERVER["REQUEST_METHOD"] !== "POST"){
  http_response_code(405);
  echo json_encode(["ok"=>false, "message"=>"Method not allowed"]);
  exit;
}

$user_id = (int)$_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data["id"] ?? 0);
$mark_all = (int)($data["mark_all"] ?? 0);

if($mark_all === 1){
  $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
  if(!$stmt){
    echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]); exit;
  }
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  echo json_encode(["ok"=>true, "message"=>"All notifications marked as read"]);
  exit;
}

if($id <= 0){
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid notification id"]);
  exit;
}

$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
if(!$stmt){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]);
  exit;
}
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

if($stmt->affected_rows === 0){
  echo json_encode(["ok"=>false, "message"=>"Not found or already read"]);
  exit;
}

echo json_encode(["ok"=>true, "message"=>"Marked as read"]);
