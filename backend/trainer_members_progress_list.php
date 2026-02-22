<?php
session_start();
header("Content-Type: application/json");

if (empty($_SESSION["logged_in"]) || ($_SESSION["role"] ?? "") !== "trainer") {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

/*
  List members + counts
  - completed_count: how many completed workouts
  - active_count: how many started workouts (not completed)
*/

$sql = "
SELECT
  u.id,
  CONCAT(u.first_name, ' ', u.last_name) AS name,
  u.email,
  SUM(CASE WHEN uw.status='completed' THEN 1 ELSE 0 END) AS completed_count,
  SUM(CASE WHEN uw.status='started' THEN 1 ELSE 0 END) AS active_count
FROM users u
LEFT JOIN user_workouts uw ON uw.user_id = u.id
WHERE u.role = 'member'
GROUP BY u.id
ORDER BY u.id DESC
";

$res = $conn->query($sql);
$items = [];
while($row = $res->fetch_assoc()){
  $items[] = [
    "id" => (int)$row["id"],
    "name" => trim($row["name"] ?: "Member"),
    "email" => $row["email"],
    "completed_count" => (int)($row["completed_count"] ?? 0),
    "active_count" => (int)($row["active_count"] ?? 0),
  ];
}

echo json_encode(["ok"=>true,"items"=>$items]);