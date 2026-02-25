<?php
header("Content-Type: application/json");
session_start();
require_once __DIR__ . "/db.php";

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if (empty($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
  respond(["ok"=>false,"message"=>"Unauthorized"], 401);
}

$code          = strtolower(trim($_POST["code"] ?? ""));
$monthly_price = (float)($_POST["monthly_price"] ?? 0);
$duration_days = (int)($_POST["duration_days"] ?? 30);
$status        = strtolower(trim($_POST["status"] ?? "active"));

if ($code === "") respond(["ok"=>false,"message"=>"Plan code required"], 400);
if ($monthly_price < 0) respond(["ok"=>false,"message"=>"Invalid price"], 400);
if ($duration_days <= 0) $duration_days = 30;
if (!in_array($status, ["active","inactive"], true)) $status = "active";

$stmt = $conn->prepare("INSERT INTO plans (code, monthly_price, duration_days, status) VALUES (?,?,?,?)");
if (!$stmt) respond(["ok"=>false,"message"=>"Prepare failed","db_error"=>$conn->error], 500);

$stmt->bind_param("sdis", $code, $monthly_price, $duration_days, $status);
$ok = $stmt->execute();

if (!$ok) {
  $http = ((int)$conn->errno === 1062) ? 409 : 500;
  $msg  = ((int)$conn->errno === 1062) ? "Plan code already exists" : "Create failed";
  $stmtErr = $stmt->error;
  $stmt->close();
  respond(["ok"=>false,"message"=>$msg,"db_error"=>$conn->error,"stmt_error"=>$stmtErr], $http);
}

$newId = $conn->insert_id;
$stmt->close();
respond(["ok"=>true,"id"=>$newId,"message"=>"Created"]);