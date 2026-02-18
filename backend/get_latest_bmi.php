<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

$role = strtolower($_SESSION["role"] ?? "");
if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"]) || $role !== "member") {
  respond(["ok"=>false, "message"=>"Login required"], 401);
}

$user_id = (int)$_SESSION["user_id"];

// table exists?
$chk = $conn->query("SHOW TABLES LIKE 'bmi_records'");
if(!$chk || $chk->num_rows === 0){
  respond(["ok"=>true, "latest"=>null]);
}

$st = $conn->prepare("
  SELECT height_cm, weight_kg, bmi_value, category, created_at
  FROM bmi_records
  WHERE user_id=?
  ORDER BY created_at DESC
  LIMIT 1
");
if(!$st) respond(["ok"=>false, "message"=>"Prepare failed", "error"=>$conn->error], 500);

$st->bind_param("i", $user_id);
$st->execute();
$res = $st->get_result();
$row = $res ? $res->fetch_assoc() : null;
$st->close();

respond(["ok"=>true, "latest"=>$row ?: null]);
