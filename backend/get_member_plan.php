<?php
session_start();
require_once "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "ok" => false,
        "message" => "Not logged in"
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];

/*
  Latest payment → plan → plans table join
*/
$sql = "
    SELECT 
        p.plan,
        p.billing_cycle,
        pl.name AS plan_name,
        pl.monthly_price
    FROM payments p
    JOIN plans pl ON pl.code = p.plan
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "ok" => true,
        "plan" => $row
    ]);
} else {
    echo json_encode([
        "ok" => true,
        "plan" => null
    ]);
}
