<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$role = trim($data['role'] ?? 'member');

if ($email === '' || $password === '') {
  http_response_code(400);
  echo json_encode(["ok" => false, "message" => "Email & password required"]);
  exit;
}

$stmt = $conn->prepare("SELECT id, role, first_name, last_name, email, password FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Invalid credentials"]);
  exit;
}

$user = $res->fetch_assoc();

if ($role !== $user['role']) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Role mismatch. Select correct role."]);
  exit;
}

if (!password_verify($password, $user['password'])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Invalid credentials"]);
  exit;
}

$fullName = trim($user['first_name'] . " " . $user['last_name']);

$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['username'] = $fullName;
$_SESSION['role'] = $user['role'];

echo json_encode(["ok" => true, "role" => $user['role']]);
exit;
