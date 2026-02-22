<?php
// backend/admin_get_stats.php
session_start();
header('Content-Type: application/json');

// ✅ Only admin
if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok" => false, "message" => "Forbidden"]);
  exit;
}

// ✅ DB connect (change this require if your file name differs)
require_once __DIR__ . '/db.php'; 
// Expect: $conn = new mysqli(...)

function countQ($conn, $sql) {
  $res = $conn->query($sql);
  if (!$res) return 0;
  $row = $res->fetch_row();
  return (int)($row[0] ?? 0);
}

$stats = [
  "total_users"     => countQ($conn, "SELECT COUNT(*) FROM users"),
  "total_members"   => countQ($conn, "SELECT COUNT(*) FROM users WHERE role='member'"),
  "total_trainers"  => countQ($conn, "SELECT COUNT(*) FROM users WHERE role='trainer'"),
  "total_admins"    => countQ($conn, "SELECT COUNT(*) FROM users WHERE role='admin'"),

  "workouts_count"  => countQ($conn, "SELECT COUNT(*) FROM workouts"),
  "completions_count" => countQ($conn, "SELECT COUNT(*) FROM user_workouts WHERE status='completed'"),

  "feedback_count"  => countQ($conn, "SELECT COUNT(*) FROM feedback"),
];

echo json_encode(["ok" => true, "stats" => $stats]);
