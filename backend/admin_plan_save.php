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

$id            = (int)($_POST["id"] ?? 0);
$code_raw      = $_POST["code"] ?? "";
$code          = strtolower(trim($code_raw));
$monthly_price = (float)($_POST["monthly_price"] ?? 0);
$duration_days = (int)($_POST["duration_days"] ?? 30);
$status        = strtolower(trim($_POST["status"] ?? "active"));

if ($code === "") {
  respond(["ok"=>false,"message"=>"Plan code required"], 400);
}
if ($monthly_price < 0) {
  respond(["ok"=>false,"message"=>"Invalid price"], 400);
}
if ($duration_days <= 0) $duration_days = 30;
if (!in_array($status, ["active","inactive"], true)) $status = "active";

/** helper: duplicate detection */
function isDuplicateCodeError($conn): bool {
  // 1062 = Duplicate entry
  return ((int)($conn->errno ?? 0) === 1062);
}

if ($id > 0) {
  $stmt = $conn->prepare("UPDATE plans SET code=?, monthly_price=?, duration_days=?, status=? WHERE id=?");
  if (!$stmt) {
    respond(["ok"=>false,"message"=>"Prepare failed","db_error"=>$conn->error], 500);
  }

  $stmt->bind_param("sdisi", $code, $monthly_price, $duration_days, $status, $id);
  $ok = $stmt->execute();

  if (!$ok) {
    $msg = isDuplicateCodeError($conn) ? "Plan code already exists" : "Update failed";
    $http = isDuplicateCodeError($conn) ? 409 : 500;
    $stmtErr = $stmt->error;
    $stmt->close();
    respond(["ok"=>false,"message"=>$msg,"db_error"=>$conn->error,"stmt_error"=>$stmtErr], $http);
  }

  $stmt->close();
  respond(["ok"=>true,"id"=>$id,"message"=>"Updated"]);
}

/** CREATE */
$stmt = $conn->prepare("INSERT INTO plans (code, monthly_price, duration_days, status) VALUES (?,?,?,?)");
if (!$stmt) {
  respond(["ok"=>false,"message"=>"Prepare failed","db_error"=>$conn->error], 500);
}

$stmt->bind_param("sdis", $code, $monthly_price, $duration_days, $status);
$ok = $stmt->execute();

if (!$ok) {
  $msg = isDuplicateCodeError($conn) ? "Plan code already exists" : "Create failed";
  $http = isDuplicateCodeError($conn) ? 409 : 500;
  $stmtErr = $stmt->error;
  $stmt->close();
  respond(["ok"=>false,"id"=>0,"message"=>$msg,"db_error"=>$conn->error,"stmt_error"=>$stmtErr], $http);
}

$newId = $conn->insert_id;
$stmt->close();
respond(["ok"=>true,"id"=>$newId,"message"=>"Created"]);