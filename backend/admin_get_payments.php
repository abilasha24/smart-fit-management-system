<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Unauthorized"]);
  exit;
}

require_once __DIR__ . "/db.php"; // make sure $conn (mysqli) exists

// Inputs
$q      = isset($_GET['q']) ? trim($_GET['q']) : "";
$plan   = isset($_GET['plan']) ? trim($_GET['plan']) : "all";
$month  = isset($_GET['month']) ? trim($_GET['month']) : "all"; // format: YYYY-MM
$status = isset($_GET['status']) ? trim($_GET['status']) : "all"; // paid/free

// Build WHERE
$where = [];
$params = [];
$types = "";

// Search (email / method / cycle / plan)
if ($q !== "") {
  $where[] = "(user_email LIKE ? OR payment_method LIKE ? OR billing_cycle LIKE ? OR plan LIKE ?)";
  $like = "%".$q."%";
  $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "ssss";
}

// Plan
if ($plan !== "" && $plan !== "all") {
  $where[] = "plan = ?";
  $params[] = $plan;
  $types .= "s";
}

// Month filter (created_at)
if ($month !== "" && $month !== "all") {
  // Expect YYYY-MM
  $where[] = "DATE_FORMAT(created_at, '%Y-%m') = ?";
  $params[] = $month;
  $types .= "s";
}

// Status (computed)
if ($status !== "" && $status !== "all") {
  if ($status === "paid") {
    $where[] = "amount > 0";
  } elseif ($status === "free") {
    $where[] = "amount = 0";
  }
}

$whereSql = count($where) ? ("WHERE " . implode(" AND ", $where)) : "";

// ---------- Main list query (with computed status) ----------
$sql = "
  SELECT
    id, user_id, user_email, plan, billing_cycle, payment_method, amount, created_at,
    CASE WHEN amount > 0 THEN 'paid' ELSE 'free' END AS status
  FROM payments
  $whereSql
  ORDER BY created_at DESC, id DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(["ok"=>false, "message"=>"Prepare failed (list)"]);
  exit;
}

if ($types !== "") $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$payments = [];
while ($row = $res->fetch_assoc()) {
  $payments[] = $row;
}
$stmt->close();

// ---------- Totals (overall) ----------
$totSql = "
  SELECT
    COALESCE(SUM(amount),0) AS total_revenue,
    COALESCE(SUM(CASE WHEN amount>0 THEN 1 ELSE 0 END),0) AS paid_transactions
  FROM payments
";
$totRes = $conn->query($totSql);
$tot = $totRes ? $totRes->fetch_assoc() : ["total_revenue"=>0, "paid_transactions"=>0];

// ---------- Totals (this month) ----------
$monthSql = "
  SELECT
    COALESCE(SUM(amount),0) AS this_month_revenue
  FROM payments
  WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
";
$monthRes = $conn->query($monthSql);
$mrow = $monthRes ? $monthRes->fetch_assoc() : ["this_month_revenue"=>0];

// ---------- Months list for dropdown ----------
$monthsSql = "
  SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym
  FROM payments
  GROUP BY ym
  ORDER BY ym DESC
";
$monthsRes = $conn->query($monthsSql);
$months = [];
if ($monthsRes) {
  while ($r = $monthsRes->fetch_assoc()) $months[] = $r["ym"];
}

echo json_encode([
  "ok" => true,
  "payments" => $payments,
  "stats" => [
    "total_revenue" => (float)$tot["total_revenue"],
    "this_month_revenue" => (float)$mrow["this_month_revenue"],
    "paid_transactions" => (int)$tot["paid_transactions"]
  ],
  "months" => $months
]);