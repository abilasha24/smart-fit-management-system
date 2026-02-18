<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

function respond($arr, $code = 200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  respond(["status"=>"error","message"=>"Invalid request"], 405);
}

$email    = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");
$role     = strtolower(trim($_POST["role"] ?? "member"));
if (!in_array($role, ["member","trainer","admin"], true)) $role = "member";

if ($email === "" || $password === "") {
  respond(["status"=>"error","message"=>"Email and password required"], 400);
}

$stmt = $conn->prepare("
  SELECT id, first_name, last_name, email, password_hash, role
  FROM users
  WHERE email=? AND role=?
  LIMIT 1
");
if (!$stmt) respond(["status"=>"error","message"=>"Prepare failed: ".$conn->error], 500);

$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
  $stmt->close();
  respond(["status"=>"error","message"=>"Invalid email / role"], 401);
}

$u = $res->fetch_assoc();
$stmt->close();

if (!password_verify($password, $u["password_hash"])) {
  respond(["status"=>"error","message"=>"Incorrect password"], 401);
}

/* ✅ SESSION SET HERE */
$_SESSION["logged_in"] = true;
$_SESSION["user_id"]   = (int)$u["id"];
$_SESSION["role"]      = $u["role"];
$_SESSION["email"]     = $u["email"];

// ✅ Create full name safely
$fullName = trim(($u["first_name"] ?? "") . " " . ($u["last_name"] ?? ""));
if ($fullName === "") $fullName = $u["email"]; // fallback if names empty

// ✅ IMPORTANT: set both keys to match whoami + front-end
$_SESSION["name"]      = $fullName;          // whoami.php uses this
$_SESSION["full_name"] = $fullName;          // keep your existing key too

respond([
  "status"    => "success",
  "ok"        => true,
  "user_id"   => (int)$u["id"],
  "role"      => $u["role"],
  "name"      => $fullName,          // ✅ frontend can use me.name
  "full_name" => $fullName
]);
