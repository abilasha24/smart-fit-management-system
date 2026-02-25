<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["ok"=>false, "message"=>"Method not allowed"]);
  exit;
}

require_once __DIR__ . "/db.php";

$body = json_decode(file_get_contents("php://input"), true);
$user_id = (int)($body['user_id'] ?? 0);
$role    = trim($body['role'] ?? '');
$plan    = trim($body['plan'] ?? '');

if ($user_id <= 0) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid user_id"]);
  exit;
}

$allowed_roles = ['member','trainer','admin'];
$allowed_plans = ['basic','premium','pro'];

if ($role !== '' && !in_array($role, $allowed_roles, true)) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid role"]);
  exit;
}

if ($plan !== '' && !in_array($plan, $allowed_plans, true)) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid plan"]);
  exit;
}

// ✅ optional: prevent editing own role (safer)
if ($user_id === (int)($_SESSION['user_id'] ?? 0) && $role !== '') {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"You cannot change your own role"]);
  exit;
}

$fields = [];
$params = [];
$types = "";

if ($role !== '') { $fields[] = "role=?"; $params[] = $role; $types .= "s"; }
if ($plan !== '') { $fields[] = "plan=?"; $params[] = $plan; $types .= "s"; }

if (empty($fields)) {
  echo json_encode(["ok"=>true, "message"=>"Nothing to update"]);
  exit;
}

$params[] = $user_id;
$types .= "i";

$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id=?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Prepare failed"]);
  exit;
}

$stmt->bind_param($types, ...$params);

// ✅ Build audit action text (what changed)
$actionParts = [];
if ($role !== '') $actionParts[] = "role -> {$role}";
if ($plan !== '') $actionParts[] = "plan -> {$plan}";
$auditAction = "Updated user (" . implode(", ", $actionParts) . ")";

// ✅ action_type (for filtering)
$actionType = "user_update";

$admin_id = (int)($_SESSION['user_id'] ?? 0);

try {
  // ✅ Transaction (update + audit log together)
  $conn->begin_transaction();

  if ($stmt->execute()) {
    $stmt->close();

    // ✅ Insert audit log (NOW includes action_type)
    $log = $conn->prepare("
      INSERT INTO audit_logs (admin_id, action_type, action, target_user_id)
      VALUES (?, ?, ?, ?)
    ");

    if (!$log) {
      $conn->rollback();
      http_response_code(500);
      echo json_encode(["ok"=>false, "message"=>"Audit prepare failed"]);
      exit;
    }

    // admin_id (i), action_type (s), action (s), target_user_id (i)
    $log->bind_param("issi", $admin_id, $actionType, $auditAction, $user_id);

    if (!$log->execute()) {
      $log->close();
      $conn->rollback();
      http_response_code(500);
      echo json_encode(["ok"=>false, "message"=>"Audit insert failed"]);
      exit;
    }

    $log->close();
    $conn->commit();

    echo json_encode(["ok"=>true]);
    exit;
  }

  $stmt->close();
  $conn->rollback();
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"DB error"]);
  exit;

} catch (Throwable $e) {
  if (isset($stmt) && $stmt) $stmt->close();
  $conn->rollback();
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Server error"]);
  exit;
}