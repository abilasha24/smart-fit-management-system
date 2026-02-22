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
$workout_id = (int)($_POST["workout_id"] ?? 0);

if ($workout_id <= 0) {
  echo json_encode(["ok"=>false,"message"=>"workout_id required"]);
  exit;
}

$check = $conn->prepare("SELECT id, status FROM user_workouts WHERE user_id=? AND workout_id=? ORDER BY id DESC LIMIT 1");
if(!$check){
  echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
  exit;
}
$check->bind_param("ii", $user_id, $workout_id);
$check->execute();
$res = $check->get_result();
$row = $res->fetch_assoc();
$check->close();

if ($row) {
  $id = (int)$row["id"];

  $upd = $conn->prepare("
    UPDATE user_workouts
    SET status='completed',
        completed_at = NOW(),
        started_at = COALESCE(started_at, NOW())
    WHERE id=?
  ");
  if(!$upd){
    echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
    exit;
  }
  $upd->bind_param("i", $id);
  $ok = $upd->execute();
  $upd->close();

  if(!$ok){
    echo json_encode(["ok"=>false,"message"=>"Update failed"]);
    exit;
  }

  echo json_encode(["ok"=>true,"message"=>"Completed (updated)"]);
  exit;
}

// If no row exists, insert completed record directly
$ins = $conn->prepare("
  INSERT INTO user_workouts (user_id, workout_id, status, started_at, completed_at, created_at)
  VALUES (?, ?, 'completed', NOW(), NOW(), NOW())
");
if(!$ins){
  echo json_encode(["ok"=>false,"message"=>"Prepare failed: ".$conn->error]);
  exit;
}
$ins->bind_param("ii", $user_id, $workout_id);
$ok = $ins->execute();
$ins->close();

if(!$ok){
  echo json_encode(["ok"=>false,"message"=>"Insert failed"]);
  exit;
}

echo json_encode(["ok"=>true,"message"=>"Completed (inserted)"]);