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

if($_SERVER["REQUEST_METHOD"] !== "POST"){
  respond(["ok"=>false,"message"=>"Method not allowed"], 405);
}

$me_id = (int)$_SESSION["user_id"];

/* ✅ Accept JSON input safely */
$raw = file_get_contents("php://input");
$input = json_decode($raw, true);
if(!is_array($input)) $input = [];

$title = trim($input["title"] ?? "");
$date  = trim($input["date"] ?? "");
$time  = trim($input["time"] ?? "");    // optional
$notes = trim($input["notes"] ?? "");

$user_id = (int)($input["user_id"] ?? 0); // trainer sends member id

if($title === "" || $date === ""){
  respond(["ok"=>false,"message"=>"Title & date required"], 400);
}

if($role === "member"){
  // member can only add their own schedule
  $user_id = $me_id;
  $trainer_id = null;
  $created_by = "member";
}else{
  // trainer must choose member
  if($user_id <= 0){
    respond(["ok"=>false,"message"=>"Missing member user_id"], 400);
  }
  $trainer_id = $me_id;
  $created_by = "trainer";
}

if($time === "") $time = null;
if($notes === "") $notes = null;

/* ✅ Insert schedule */
$stmt = $conn->prepare("
  INSERT INTO schedules (user_id, trainer_id, title, schedule_date, schedule_time, notes, created_by_role, created_at)
  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");
if(!$stmt){
  respond(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error], 500);
}

$stmt->bind_param(
  "iisssss",
  $user_id,
  $trainer_id,
  $title,
  $date,
  $time,
  $notes,
  $created_by
);

$ok = $stmt->execute();
if(!$ok){
  respond(["ok"=>false,"message"=>"Insert failed","error"=>$stmt->error], 500);
}

$schedule_id = (int)$conn->insert_id;

/* ✅ NEW: If trainer created schedule → create member notification */
if($role === "trainer"){
  $notifTitle = "New schedule added";
  $notifMsg   = "Trainer scheduled: " . $title . " (" . $date . ($time ? (" " . $time) : "") . ")";

  $nt = $conn->prepare("
    INSERT INTO notifications (user_id, title, message, is_read, created_at)
    VALUES (?, ?, ?, 0, NOW())
  ");

  // If prepare fails, don't break schedule flow—just skip notification
  if($nt){
    $nt->bind_param("iss", $user_id, $notifTitle, $notifMsg);
    $nt->execute();
  }
}

/* ✅ Return response */
respond([
  "ok"=>true,
  "id"=>$schedule_id
]);