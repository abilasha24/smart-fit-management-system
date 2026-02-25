<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

function respond($arr, $code = 200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  respond(["ok"=>false, "message"=>"Invalid request method"], 405);
}

$body = json_decode(file_get_contents("php://input"), true);
$first_name = trim($body["first_name"] ?? "");
$last_name  = trim($body["last_name"] ?? "");
$email      = trim($body["email"] ?? "");
$phone      = trim($body["phone"] ?? "");
$password   = (string)($body["password"] ?? "");
$plan       = trim($body["plan"] ?? "basic");

if ($first_name === "" || $last_name === "" || $email === "" || $password === "") {
  respond(["ok"=>false, "message"=>"First name, last name, email, password required"], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  respond(["ok"=>false, "message"=>"Invalid email"], 400);
}

$allowed_plans = ["basic","premium","pro"];
if (!in_array($plan, $allowed_plans, true)) $plan = "basic";

// Check email already exists
$chk = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
if (!$chk) respond(["ok"=>false, "message"=>"Prepare failed"], 500);
$chk->bind_param("s", $email);
$chk->execute();
$res = $chk->get_result();
if ($res && $res->num_rows > 0) {
  $chk->close();
  respond(["ok"=>false, "message"=>"Email already exists"], 409);
}
$chk->close();

// Insert trainer
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = "trainer";
$status = "active";

$stmt = $conn->prepare("
  INSERT INTO users (first_name, last_name, email, phone, password_hash, role, plan, status, created_at)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
if (!$stmt) respond(["ok"=>false, "message"=>"Prepare failed"], 500);

$stmt->bind_param("ssssssss", $first_name, $last_name, $email, $phone, $hash, $role, $plan, $status);

if ($stmt->execute()) {
  respond(["ok"=>true, "id"=>$stmt->insert_id]);
} else {
  respond(["ok"=>false, "message"=>"DB error"], 500);
}