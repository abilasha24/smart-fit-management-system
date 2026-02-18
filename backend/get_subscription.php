<?php
// backend/get_subscription.php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

// member auth
$role = strtolower(trim($_SESSION["role"] ?? ""));
if (!isset($_SESSION["user_id"]) || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

$user_id = (int)$_SESSION["user_id"];

$sql = "
SELECT plan, billing_cycle, amount, payment_method, created_at
FROM payments
WHERE user_id = ?
ORDER BY created_at DESC
LIMIT 1
";

$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]);
  exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
  echo json_encode(["ok"=>true, "subscription"=>null]);
  exit;
}

echo json_encode([
  "ok"=>true,
  "subscription"=>$res->fetch_assoc()
]);
