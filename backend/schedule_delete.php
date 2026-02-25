<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
  respond(["ok"=>false,"message"=>"Not logged in"], 401);
}

$role = strtolower(trim($_SESSION["role"] ?? ""));
if(!in_array($role, ["trainer","member"])) {
  respond(["ok"=>false,"message"=>"Forbidden"], 403);
}

if($_SERVER["REQUEST_METHOD"] !== "POST"){
  respond(["ok"=>false,"message"=>"Method not allowed"], 405);
}

$me_id = (int)$_SESSION["user_id"];
$input = json_decode(file_get_contents("php://input"), true);
$id = (int)($input["id"] ?? 0);

if($id <= 0) respond(["ok"=>false,"message"=>"Missing id"], 400);

if($role === "member"){
  // member can delete only their own schedules (usually their own created items)
  $stmt = $conn->prepare("DELETE FROM schedules WHERE id=? AND user_id=?");
  $stmt->bind_param("ii", $id, $me_id);
} else {
  // trainer can delete only schedules they created (trainer_id = me)
  $stmt = $conn->prepare("DELETE FROM schedules WHERE id=? AND trainer_id=?");
  $stmt->bind_param("ii", $id, $me_id);
}

if(!$stmt) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);

$stmt->execute();
if($stmt->affected_rows === 0){
  respond(["ok"=>false,"message"=>"Not found / no permission"], 404);
}
respond(["ok"=>true]);