<?php
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
  echo json_encode(["ok"=>false,"message"=>"Not logged in"]);
  exit;
}

if (strtolower($_SESSION["role"] ?? "") !== "trainer") {
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

$trainer_id = intval($_SESSION["user_id"]);

$stmt = $conn->prepare("
  SELECT
    uw.id,
    uw.status,
    uw.assigned_at,
    u.email AS member_email,
    w.title AS workout_title
  FROM user_workouts uw
  JOIN users u ON u.id = uw.user_id
  JOIN workouts w ON w.id = uw.workout_id
  WHERE uw.trainer_id = ?
  ORDER BY uw.id DESC
  LIMIT 10
");

if(!$stmt){
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed"]);
  exit;
}

$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()){
  $items[] = [
    "id" => $row["id"],
    "status" => $row["status"] ?: "assigned",
    "assigned_at" => $row["assigned_at"],
    "member_name" => $row["member_email"],
    "workout_title" => $row["workout_title"]
  ];
}

echo json_encode(["ok"=>true,"items"=>$items]);
exit;