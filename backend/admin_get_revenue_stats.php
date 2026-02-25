<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

// total revenue
$r1 = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM payments");
$total = (float)($r1->fetch_assoc()['s'] ?? 0);

// this month revenue
$r2 = $conn->query("SELECT COALESCE(SUM(amount),0) AS s
                    FROM payments
                    WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
$this_month = (float)($r2->fetch_assoc()['s'] ?? 0);

// paid tx count
$r3 = $conn->query("SELECT COUNT(*) AS c FROM payments WHERE amount > 0");
$paid_tx = (int)($r3->fetch_assoc()['c'] ?? 0);

// active subscribers (users table plan not null)
$r4 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='member' AND plan IS NOT NULL AND plan <> ''");
$active_members = (int)($r4->fetch_assoc()['c'] ?? 0);

echo json_encode([
  "ok" => true,
  "stats" => [
    "total_revenue" => $total,
    "this_month_revenue" => $this_month,
    "paid_transactions" => $paid_tx,
    "active_members" => $active_members
  ]
]);