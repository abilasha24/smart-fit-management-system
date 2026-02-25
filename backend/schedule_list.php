<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
  respond(["ok"=>false,"message"=>"Not logged in"], 401);
}

$role = strtolower(trim($_SESSION["role"] ?? ""));
if(!in_array($role, ["trainer","member"])) {
  respond(["ok"=>false,"message"=>"Forbidden"], 403);
}

$me_id = (int)$_SESSION["user_id"];

// optional filter for trainer: ?user_id=123
$user_id_filter = (int)($_GET["user_id"] ?? 0);

$selectCols = "
  SELECT id, user_id, trainer_id, title,
         DATE_FORMAT(schedule_date,'%Y-%m-%d') AS schedule_date,
         IFNULL(TIME_FORMAT(schedule_time,'%H:%i'),'') AS schedule_time,
         IFNULL(notes,'') AS notes,
         created_by_role,
         status,
         IFNULL(DATE_FORMAT(completed_at,'%Y-%m-%d %H:%i:%s'),'') AS completed_at,
         DATE_FORMAT(created_at,'%Y-%m-%d %H:%i:%s') AS created_at
  FROM schedules
";

if($role === "member"){
  // member sees only own schedules
  $user_id_filter = $me_id;

  $stmt = $conn->prepare("
    $selectCols
    WHERE user_id=?
    ORDER BY schedule_date DESC, schedule_time DESC, id DESC
  ");
  if(!$stmt) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);
  $stmt->bind_param("i", $user_id_filter);

} else {
  // trainer sees schedules created by them (trainer_id = me)
  // if user_id filter provided -> only that member
  if($user_id_filter > 0){
    $stmt = $conn->prepare("
      $selectCols
      WHERE trainer_id=? AND user_id=?
      ORDER BY schedule_date DESC, schedule_time DESC, id DESC
    ");
    if(!$stmt) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);
    $stmt->bind_param("ii", $me_id, $user_id_filter);

  } else {
    $stmt = $conn->prepare("
      $selectCols
      WHERE trainer_id=?
      ORDER BY schedule_date DESC, schedule_time DESC, id DESC
    ");
    if(!$stmt) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);
    $stmt->bind_param("i", $me_id);
  }
}

$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()){
  // normalize some fields
  $row["id"] = (int)$row["id"];
  $row["user_id"] = (int)$row["user_id"];
  $row["trainer_id"] = (int)($row["trainer_id"] ?? 0);
  $row["status"] = $row["status"] ?: "pending";
  $items[] = $row;
}

respond(["ok"=>true, "items"=>$items]);