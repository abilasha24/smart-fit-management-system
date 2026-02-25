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
$name = trim($data["username"] ?? ""); // front-end field remains "username"

if ($name === "" || mb_strlen($name) < 3) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Name must be at least 3 characters"]);
  exit;
}

/**
 * ✅ users table-ல் username column இல்லை.
 * ✅ simplest update: first_name only
 */
$stmt = $conn->prepare("UPDATE users SET first_name=? WHERE id=?");
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Prepare failed", "error"=>$conn->error]);
  exit;
}

$stmt->bind_param("si", $name, $user_id);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Failed to update profile"]);
  exit;
}

/* optional: update session display name */
$_SESSION["name"] = $name;

echo json_encode(["ok"=>true, "message"=>"Profile updated"]);
exit;