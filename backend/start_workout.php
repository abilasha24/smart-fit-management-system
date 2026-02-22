<?php
session_start();
header("Content-Type: application/json");

if (empty($_SESSION["logged_in"]) || ($_SESSION["role"] ?? "") !== "member") {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";
$user_id = (int)$_SESSION["user_id"];

$sql = "
  SELECT
    uw.workout_id,
    uw.status,
    COALESCE(uw.completed_at, uw.started_at, uw.assigned_at, uw.created_at) AS date,
    w.title, w.level, w.duration_min, w.calories
  FROM user_workouts uw
  JOIN workouts w ON w.id = uw.workout_id
  WHERE uw.user_id=?
  ORDER BY COALESCE(uw.completed_at, uw.started_at, uw.assigned_at, uw.created_at) DESC
";

$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
  exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()){
  $items[] = $row;
}
$stmt->close();

echo json_encode(["ok"=>true,"items"=>$items]);