<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

function table_exists($conn, $table){
  $sql = "SHOW TABLES LIKE ?";
  $st = $conn->prepare($sql);
  if(!$st) return false;
  $st->bind_param("s", $table);
  $st->execute();
  $r = $st->get_result();
  $ok = ($r && $r->num_rows > 0);
  $st->close();
  return $ok;
}

if (empty($_SESSION["logged_in"])) {
  respond(["ok"=>false, "message"=>"Not logged in"], 401);
}

$userId = (int)($_SESSION["user_id"] ?? 0);
if ($userId <= 0) respond(["ok"=>false, "message"=>"Invalid session user"], 401);

/* ✅ Defaults */
$completed = 0;
$started = 0;
$totalMinutes = 0;
$totalCalories = 0;

/*
  Your project files show:
  - get_my_workouts.php
  - save_member_workout.php
  - complete_workout.php
  So common table name: member_workouts
  We'll try member_workouts first; if not exist => return zeros.
*/
if (table_exists($conn, "member_workouts")) {

  // Completed count
  $st = $conn->prepare("SELECT COUNT(*) c FROM member_workouts WHERE user_id=? AND status='completed'");
  if ($st){
    $st->bind_param("i", $userId);
    $st->execute();
    $completed = (int)($st->get_result()->fetch_assoc()["c"] ?? 0);
    $st->close();
  }

  // Started count
  $st = $conn->prepare("SELECT COUNT(*) c FROM member_workouts WHERE user_id=? AND status='started'");
  if ($st){
    $st->bind_param("i", $userId);
    $st->execute();
    $started = (int)($st->get_result()->fetch_assoc()["c"] ?? 0);
    $st->close();
  }

  // Total minutes + calories (if columns exist, else safe 0)
  // Try common columns: duration_min, calories
  $st = $conn->prepare("SELECT
      COALESCE(SUM(duration_min),0) AS mins,
      COALESCE(SUM(calories),0) AS cals
    FROM member_workouts
    WHERE user_id=? AND status='completed'");
  if ($st){
    $st->bind_param("i", $userId);
    $st->execute();
    $row = $st->get_result()->fetch_assoc() ?? [];
    $totalMinutes = (int)($row["mins"] ?? 0);
    $totalCalories = (int)($row["cals"] ?? 0);
    $st->close();
  }
}

respond([
  "ok" => true,
  "stats" => [
    "completed" => $completed,
    "started" => $started,
    "total_minutes" => $totalMinutes,
    "total_calories" => $totalCalories
  ]
]);
