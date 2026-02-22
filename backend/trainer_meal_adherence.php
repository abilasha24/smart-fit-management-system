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
$limit = (int)($_GET['limit'] ?? 8);
if ($limit < 1) $limit = 8;
if ($limit > 20) $limit = 20;

$where = "u.role='member'";
$params = [];
$types = "";

if ($q !== "") {
  $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
  $like = "%".$q."%";
  $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "sss";
}

$sql = "
  SELECT
    u.id AS user_id,
    CONCAT(u.first_name,' ',u.last_name) AS name,
    u.email,

    COALESCE((
      SELECT COUNT(DISTINCT mpl.log_date)
      FROM meal_plan_logs mpl
      WHERE mpl.user_id = u.id
        AND mpl.log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ),0) AS last7_days,

    COALESCE((
      SELECT COUNT(*)
      FROM meal_plan_logs mpl2
      WHERE mpl2.user_id = u.id
        AND mpl2.log_date = CURDATE()
      LIMIT 1
    ),0) AS today_logs,

    COALESCE((
      SELECT MAX(mpl3.log_date)
      FROM meal_plan_logs mpl3
      WHERE mpl3.user_id = u.id
    ),NULL) AS last_follow_date

  FROM users u
  WHERE $where
  ORDER BY last7_days DESC, last_follow_date DESC
  LIMIT $limit
";

$stmt = $conn->prepare($sql);
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed: ".$conn->error]);
  exit;
}

if ($types !== "") $stmt->bind_param($types, ...$params);

$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($r = $res->fetch_assoc()){
  $last7 = (int)$r["last7_days"];
  $items[] = [
    "user_id" => (int)$r["user_id"],
    "name" => trim($r["name"]) ?: "Member",
    "email" => $r["email"],
    "last7_days" => $last7,
    "adherence_pct" => (int)round(($last7/7)*100),
    "followed_today" => ((int)$r["today_logs"] > 0),
    "last_follow_date" => $r["last_follow_date"]
  ];
}

echo json_encode(["ok"=>true,"items"=>$items]);