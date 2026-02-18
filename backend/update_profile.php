<?php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

$role = strtolower(trim($_SESSION["role"] ?? ""));
$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($user_id <= 0 || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode(["ok"=>false, "message"=>"Method not allowed"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data["username"] ?? "");

if ($username === "" || strlen($username) < 3) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Username must be at least 3 characters"]);
  exit;
}

// Duplicate check
$chk = $conn->prepare("SELECT id FROM users WHERE username=? AND id<>?");
$chk->bind_param("si", $username, $user_id);
$chk->execute();
$chkRes = $chk->get_result();
if ($chkRes && $chkRes->num_rows > 0) {
  echo json_encode(["ok"=>false, "message"=>"Username already taken"]);
  exit;
}

$stmt = $conn->prepare("UPDATE users SET username=? WHERE id=?");
$stmt->bind_param("si", $username, $user_id);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Failed to update profile"]);
  exit;
}

$_SESSION["username"] = $username;

echo json_encode(["ok"=>true, "message"=>"Profile updated"]);
exit;
