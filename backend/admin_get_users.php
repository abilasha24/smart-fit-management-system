<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

$q    = trim($_GET['q'] ?? '');
$role = trim($_GET['role'] ?? 'all'); // all | member | trainer | admin

$where = [];
$params = [];
$types = "";

// role filter
if ($role !== 'all' && in_array($role, ['member','trainer','admin'], true)) {
  $where[] = "role = ?";
  $params[] = $role;
  $types .= "s";
}

// search filter
if ($q !== '') {
  $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
  $like = "%".$q."%";
  array_push($params, $like, $like, $like, $like);
  $types .= "ssss";
}

$sql = "SELECT id, first_name, last_name, email, phone, role, plan, status, created_at
        FROM users";

if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);

$sql .= " ORDER BY created_at DESC, id DESC LIMIT 200";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Prepare failed"]);
  exit;
}

if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
  $rows[] = $row;
}

$stmt->close();

echo json_encode(["ok"=>true, "users"=>$rows]);
exit;