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
if ($role !== "member") {
  respond(["ok"=>false,"message"=>"Forbidden"], 403);
}

if($_SERVER["REQUEST_METHOD"] !== "POST"){
  respond(["ok"=>false,"message"=>"Method not allowed"], 405);
}

$me_id = (int)$_SESSION["user_id"];

$raw = file_get_contents("php://input");
$input = json_decode($raw, true);
if(!is_array($input)) $input = [];

$schedule_id = (int)($input["id"] ?? 0);
if($schedule_id <= 0){
  respond(["ok"=>false,"message"=>"Missing schedule id"], 400);
}

/* 1) Verify schedule belongs to this member + get trainer_id + title/date/time */
$stmt = $conn->prepare("
  SELECT id, trainer_id, title,
         DATE_FORMAT(schedule_date,'%Y-%m-%d') AS schedule_date,
         IFNULL(TIME_FORMAT(schedule_time,'%H:%i'),'') AS schedule_time,
         status
  FROM schedules
  WHERE id=? AND user_id=?
  LIMIT 1
");
if(!$stmt) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);

$stmt->bind_param("ii", $schedule_id, $me_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if(!$row){
  respond(["ok"=>false,"message"=>"Schedule not found"], 404);
}

if(($row["status"] ?? "") === "done"){
  respond(["ok"=>true,"message"=>"Already completed"]);
}

/* 2) Mark done */
$up = $conn->prepare("UPDATE schedules SET status='done', completed_at=NOW() WHERE id=? AND user_id=?");
if(!$up) respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);

$up->bind_param("ii", $schedule_id, $me_id);
$up->execute();

/* 3) Notify trainer (if trainer_id exists) */
$trainer_id = (int)($row["trainer_id"] ?? 0);
if($trainer_id > 0){
  $tTitle = "Schedule completed";
  $tMsg = "Member #".$me_id." completed: ".$row["title"]." (".$row["schedule_date"].($row["schedule_time"]?(" ".$row["schedule_time"]):"").")";

  $nt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message, is_read, created_at)
    VALUES (?, ?, ?, 0, NOW())
  ");
  if($nt){
    $nt->bind_param("iss", $trainer_id, $tTitle, $tMsg);
    $nt->execute();
  }
}

respond(["ok"=>true,"message"=>"Marked done"]);