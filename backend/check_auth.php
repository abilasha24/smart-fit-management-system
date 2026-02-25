<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . "/db.php";

function respond(array $arr, int $code = 200): void {
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

// 1) Must be logged in
if (empty($_SESSION['user_id'])) {
  respond(["ok"=>false, "message"=>"Not logged in"], 401);
}

$user_id = (int)$_SESSION['user_id'];

// 2) Re-check status from DB (prevents blocked users from staying logged in)
$stmt = $conn->prepare("SELECT role, status, first_name, email FROM users WHERE id=? LIMIT 1");
if (!$stmt) {
  // Server error - avoid leaking details
  respond(["ok"=>false, "message"=>"Server error"], 500);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
  $stmt->close();
  session_destroy();
  respond(["ok"=>false, "message"=>"Session invalid. Please login again."], 401);
}

$u = $res->fetch_assoc();
$stmt->close();

// 3) If blocked → destroy session and deny
if (strtolower($u['status'] ?? 'active') === 'blocked') {
  session_destroy();
  respond(["ok"=>false, "message"=>"Account blocked. Contact admin."], 403);
}

// 4) Keep session role synced (optional but good)
$_SESSION['role'] = $u['role'] ?? ($_SESSION['role'] ?? '');

// 5) Return auth info
respond([
  "ok" => true,
  "user_id" => $user_id,
  "role" => $_SESSION['role'] ?? "",
  "first_name" => $u['first_name'] ?? ($_SESSION['first_name'] ?? ""),
  "email" => $u['email'] ?? ($_SESSION['email'] ?? "")
], 200);