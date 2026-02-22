<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'recent';

$where = "u.role='member'";
$params = [];
$types = "";

if ($q !== "") {
  $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
  $like = "%".$q."%";
  $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "sss";
}

$orderBy = "last_activity DESC";
if ($sort === "completed") $orderBy = "completed DESC, last_activity DESC";
if ($sort === "started")   $orderBy = "started DESC, last_activity DESC";
if ($sort === "name")      $orderBy = "u.first_name ASC, u.last_name ASC";

$sql = "
  SELECT
    u.id AS user_id,
    CONCAT(u.first_name,' ',u.last_name) AS name,
    u.email,
    SUM(CASE WHEN uw.status='started' THEN 1 ELSE 0 END) AS started,
    SUM(CASE WHEN uw.status='completed' THEN 1 ELSE 0 END) AS completed,
    MAX(COALESCE(uw.completed_at, uw.created_at)) AS last_activity
  FROM users u
  LEFT JOIN user_workouts uw ON uw.user_id = u.id
  WHERE $where
  GROUP BY u.id
  ORDER BY $orderBy
";

$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed: ".$conn->error]);
  exit;
}

if ($types !== "") $stmt->bind_param($types, ...$params);

$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($r = $res->fetch_assoc()){
  $items[] = [
    "user_id" => (int)$r["user_id"],
    "name" => $r["name"] ?: "Member",
    "email" => $r["email"],
    "started" => (int)($r["started"] ?? 0),
    "completed" => (int)($r["completed"] ?? 0),
    "last_activity" => $r["last_activity"]
  ];
}

echo json_encode(["ok"=>true,"items"=>$items]);