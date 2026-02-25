<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

function oneRow($conn, $sql){
  $res = $conn->query($sql);
  if(!$res) return null;
  return $res->fetch_assoc();
}

// Users by role
$r1 = oneRow($conn, "SELECT
  SUM(role='member') AS members,
  SUM(role='trainer') AS trainers,
  SUM(role='admin')  AS admins
FROM users");

// Totals
$r2 = oneRow($conn, "SELECT COUNT(*) AS total_users FROM users");

// Workouts total
$r3 = oneRow($conn, "SELECT COUNT(*) AS total_workouts FROM workouts");

// Completions (member_workouts done)
$r4 = oneRow($conn, "SELECT COUNT(*) AS total_completions FROM member_workouts WHERE status='done'");

// Payments summary
$r5 = oneRow($conn, "SELECT
  COUNT(*) AS payments_count,
  COALESCE(SUM(amount),0) AS total_revenue
FROM payments");

// This month revenue
$r6 = oneRow($conn, "SELECT
  COALESCE(SUM(amount),0) AS month_revenue
FROM payments
WHERE DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')");

// Feedback unread count (if you have is_read column)
$hasIsRead = $conn->query("SHOW COLUMNS FROM feedback LIKE 'is_read'");
if ($hasIsRead && $hasIsRead->num_rows > 0) {
  $r7 = oneRow($conn, "SELECT COUNT(*) AS unread_feedback FROM feedback WHERE is_read=0");
} else {
  // fallback: if no is_read column
  $r7 = ["unread_feedback" => 0];
}

// Blocked users count
$hasStatus = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($hasStatus && $hasStatus->num_rows > 0) {
  $r8 = oneRow($conn, "SELECT COUNT(*) AS blocked_users FROM users WHERE status='blocked'");
} else {
  $r8 = ["blocked_users" => 0];
}

echo json_encode([
  "ok" => true,
  "stats" => [
    "total_users"      => (int)($r2["total_users"] ?? 0),
    "members"          => (int)($r1["members"] ?? 0),
    "trainers"         => (int)($r1["trainers"] ?? 0),
    "admins"           => (int)($r1["admins"] ?? 0),
    "total_workouts"   => (int)($r3["total_workouts"] ?? 0),
    "total_completions"=> (int)($r4["total_completions"] ?? 0),
    "payments_count"   => (int)($r5["payments_count"] ?? 0),
    "total_revenue"    => (float)($r5["total_revenue"] ?? 0),
    "month_revenue"    => (float)($r6["month_revenue"] ?? 0),
    "unread_feedback"  => (int)($r7["unread_feedback"] ?? 0),
    "blocked_users"    => (int)($r8["blocked_users"] ?? 0),
  ]
]);