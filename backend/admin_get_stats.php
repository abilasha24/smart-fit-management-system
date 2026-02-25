<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

function q1($conn, $sql) {
  $res = $conn->query($sql);
  if (!$res) return 0;
  $row = $res->fetch_row();
  return $row ? (float)$row[0] : 0;
}

function q1i($conn, $sql) {
  $res = $conn->query($sql);
  if (!$res) return 0;
  $row = $res->fetch_row();
  return $row ? (int)$row[0] : 0;
}

$total_users     = q1i($conn, "SELECT COUNT(*) FROM users");
$total_members   = q1i($conn, "SELECT COUNT(*) FROM users WHERE role='member'");
$total_trainers  = q1i($conn, "SELECT COUNT(*) FROM users WHERE role='trainer'");
$total_admins    = q1i($conn, "SELECT COUNT(*) FROM users WHERE role='admin'");
$blocked_users   = q1i($conn, "SELECT COUNT(*) FROM users WHERE status='blocked'");

$workouts_count  = q1i($conn, "SELECT COUNT(*) FROM workouts");
$feedback_count  = q1i($conn, "SELECT COUNT(*) FROM feedback");

// ✅ completions (status='completed')
$completions_count = q1i($conn, "SELECT COUNT(*) FROM user_workouts WHERE status='completed'");

// ✅ revenue
$total_revenue = q1($conn, "SELECT IFNULL(SUM(amount),0) FROM payments");
$paid_txn      = q1i($conn, "SELECT COUNT(*) FROM payments WHERE amount > 0");

// ✅ this month revenue
$this_month_revenue = q1($conn, "
  SELECT IFNULL(SUM(amount),0)
  FROM payments
  WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())
");

echo json_encode([
  "ok" => true,
  "stats" => [
    "total_users" => $total_users,
    "workouts_count" => $workouts_count,
    "completions_count" => $completions_count,
    "feedback_count" => $feedback_count,

    "total_members" => $total_members,
    "total_trainers" => $total_trainers,
    "total_admins" => $total_admins,

    "total_revenue" => $total_revenue,
    "this_month_revenue" => $this_month_revenue,
    "paid_transactions" => $paid_txn,
    "blocked_users" => $blocked_users
  ]
]);