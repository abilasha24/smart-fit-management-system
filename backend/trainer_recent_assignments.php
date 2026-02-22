<?php
session_start();
header("Content-Type: application/json");

if (empty($_SESSION["logged_in"]) || ($_SESSION["role"] ?? "") !== "trainer") {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";
$trainer_id = (int)$_SESSION["user_id"];

$sql = "
  SELECT
    CONCAT(u.first_name,' ',u.last_name) AS member_name,
    u.email AS member_email,
    w.title AS workout_title,
    w.level,
    uw.status,
    DATE(COALESCE(uw.assigned_at, uw.created_at)) AS date
  FROM user_workouts uw
  JOIN users u ON u.id = uw.user_id
  JOIN workouts w ON w.id = uw.workout_id
  WHERE uw.trainer_id=?
  ORDER BY COALESCE(uw.assigned_at, uw.created_at) DESC
  LIMIT 8
";

$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
  exit;
}
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()){
  $items[] = $row;
}
$stmt->close();

echo json_encode(["ok"=>true,"items"=>$items]);