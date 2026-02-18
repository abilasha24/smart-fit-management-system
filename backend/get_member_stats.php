<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if (empty($_SESSION["logged_in"])) {
  respond(["ok"=>false, "message"=>"Not logged in"], 401);
}

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) respond(["ok"=>false, "message"=>"Invalid session user"], 401);

/* ---------- detect tracking table ---------- */
$trackingTable = null;

$res = $conn->query("SHOW TABLES LIKE 'member_workouts'");
if($res && $res->num_rows > 0){
  $trackingTable = "member_workouts";
} else {
  $res = $conn->query("SHOW TABLES LIKE 'user_workouts'");
  if($res && $res->num_rows > 0){
    $trackingTable = "user_workouts";
  }
}

if(!$trackingTable){
  respond(["ok"=>true, "stats"=>[
    "completed"=>0,
    "started"=>0,
    "total_minutes"=>0,
    "total_calories"=>0,
    "unread_notifications"=>0
  ]]);
}

/* ---------- Completed Count ---------- */
$completed = 0;
$st = $conn->prepare("
  SELECT COUNT(*) c
  FROM $trackingTable
  WHERE user_id=? AND status='completed'
");
if($st){
  $st->bind_param("i", $userId);
  $st->execute();
  $completed = (int)($st->get_result()->fetch_assoc()["c"] ?? 0);
  $st->close();
}

/* ---------- Started Count ---------- */
$started = 0;
$st = $conn->prepare("
  SELECT COUNT(*) c
  FROM $trackingTable
  WHERE user_id=? AND status='started'
");
if($st){
  $st->bind_param("i", $userId);
  $st->execute();
  $started = (int)($st->get_result()->fetch_assoc()["c"] ?? 0);
  $st->close();
}

/* ---------- Total Minutes + Calories (JOIN workouts) ---------- */
$totalMinutes = 0;
$totalCalories = 0;

$st = $conn->prepare("
  SELECT
    COALESCE(SUM(w.duration_min),0) AS mins,
    COALESCE(SUM(w.calories),0) AS cals
  FROM $trackingTable t
  JOIN workouts w ON t.workout_id = w.id
  WHERE t.user_id=? AND t.status='completed'
");

if($st){
  $st->bind_param("i", $userId);
  $st->execute();
  $row = $st->get_result()->fetch_assoc() ?? [];
  $totalMinutes = (int)($row["mins"] ?? 0);
  $totalCalories = (int)($row["cals"] ?? 0);
  $st->close();
}

/* ---------- Unread Notifications ---------- */
$unread = 0;
$res = $conn->query("SHOW TABLES LIKE 'notifications'");
if($res && $res->num_rows > 0){
  $st = $conn->prepare("
    SELECT COUNT(*) c
    FROM notifications
    WHERE user_id=? AND is_read=0
  ");
  if($st){
    $st->bind_param("i", $userId);
    $st->execute();
    $unread = (int)($st->get_result()->fetch_assoc()["c"] ?? 0);
    $st->close();
  }
}

respond([
  "ok" => true,
  "stats" => [
    "completed" => $completed,
    "started" => $started,
    "total_minutes" => $totalMinutes,
    "total_calories" => $totalCalories,
    "unread_notifications" => $unread
  ]
]);
