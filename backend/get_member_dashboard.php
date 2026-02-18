<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if (empty($_SESSION["logged_in"])) {
  respond(["ok"=>false, "message"=>"Not logged in"], 401);
}

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) respond(["ok"=>false, "message"=>"Invalid session user"], 401);

$stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, plan FROM users WHERE id=? LIMIT 1");
if (!$stmt) respond(["ok"=>false, "message"=>"SQL prepare failed: ".$conn->error], 500);

$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) respond(["ok"=>false, "message"=>"User not found"], 404);

$fullName = trim(($row["first_name"] ?? "")." ".($row["last_name"] ?? ""));
if ($fullName === "") $fullName = $row["email"];

respond([
  "ok" => true,
  "user" => [
    "id" => (int)$row["id"],
    "full_name" => $fullName,
    "email" => $row["email"],
    "role" => $row["role"],
    "plan" => $row["plan"]
  ]
]);
