<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

function respond(array $arr, int $code = 200): void {
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  respond(["ok"=>false, "status"=>"error", "message"=>"Invalid request"], 405);
}

$email    = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");
$role     = strtolower(trim($_POST["role"] ?? "member"));

if (!in_array($role, ["member","trainer","admin"], true)) $role = "member";

if ($email === "" || $password === "") {
  respond(["ok"=>false, "status"=>"error", "message"=>"Email and password required"], 400);
}

/**
 * SECURITY NOTE:
 * We fetch by email+role but return generic error messages to prevent user enumeration.
 */
$stmt = $conn->prepare("
  SELECT id, first_name, last_name, email, password_hash, role, status
  FROM users
  WHERE email=? AND role=?
  LIMIT 1
");
if (!$stmt) {
  respond(["ok"=>false, "status"=>"error", "message"=>"Server error"], 500);
}

$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
  $stmt->close();
  // Generic message (do not reveal whether email/role exists)
  respond(["ok"=>false, "status"=>"error", "message"=>"Invalid email or password"], 401);
}

$u = $res->fetch_assoc();
$stmt->close();

/**
 * ✅ BEST PRACTICE ORDER (Degree-level)
 * 1) Verify password first (prevents "blocked" as an existence signal)
 * 2) Then enforce blocked status
 */
if (!password_verify($password, $u["password_hash"])) {
  respond(["ok"=>false, "status"=>"error", "message"=>"Invalid email or password"], 401);
}

if (strtolower($u["status"] ?? "active") === "blocked") {
  respond(["ok"=>false, "status"=>"error", "message"=>"Account blocked. Contact admin."], 403);
}

/* ✅ SESSION SET HERE */
$_SESSION["logged_in"] = true;
$_SESSION["user_id"]   = (int)$u["id"];
$_SESSION["role"]      = $u["role"];
$_SESSION["email"]     = $u["email"];

// ✅ Create full name safely
$fullName = trim(($u["first_name"] ?? "") . " " . ($u["last_name"] ?? ""));
if ($fullName === "") $fullName = $u["email"]; // fallback if names empty

// ✅ Keep both keys to match whoami + front-end
$_SESSION["name"]      = $fullName;
$_SESSION["full_name"] = $fullName;

respond([
  "ok"        => true,
  "status"    => "success",
  "user_id"   => (int)$u["id"],
  "role"      => $u["role"],
  "name"      => $fullName,
  "full_name" => $fullName
]);