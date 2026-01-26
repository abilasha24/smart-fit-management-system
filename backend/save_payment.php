<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$email   = $_SESSION['email'] ?? '';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$plan   = trim($data['plan'] ?? '');
$billing= trim($data['billing_cycle'] ?? '');
$method = trim($data['payment_method'] ?? 'card');
$amount = floatval($data['amount'] ?? 0);

if ($plan === '' || $billing === '' || $amount <= 0) {
  http_response_code(400);
  echo json_encode(["ok" => false, "message" => "Plan/Billing/Amount required"]);
  exit;
}

$stmt = $conn->prepare("
  INSERT INTO payments (user_id, user_email, plan, billing_cycle, payment_method, amount)
  VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("issssd", $user_id, $email, $plan, $billing, $method, $amount);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Payment save failed"]);
  exit;
}

echo json_encode(["ok" => true, "message" => "Payment saved"]);
exit;
