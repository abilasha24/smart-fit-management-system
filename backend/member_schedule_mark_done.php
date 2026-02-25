<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/db.php";

$role = strtolower(trim($_SESSION["role"] ?? ""));
$member_id = (int)($_SESSION["user_id"] ?? 0);

if ($member_id <= 0 || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Member login required"]);
  exit;
}

$id = (int)($_POST["schedule_id"] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid schedule_id"]);
  exit;
}

/* 1) schedule row belongs to this member + get trainer_id */
$stmt = $conn->prepare("SELECT id, trainer_id, title, schedule_date FROM schedules WHERE id=? AND member_id=?");
$stmt->bind_param("ii", $id, $member_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if(!$row){
  http_response_code(404);
  echo json_encode(["ok"=>false, "message"=>"Schedule not found"]);
  exit;
}

$trainer_id = (int)$row["trainer_id"];
$title = $row["title"] ?? "Schedule";
$date  = $row["schedule_date"] ?? "";

/* 2) mark done */
$up = $conn->prepare("UPDATE schedules SET status='done' WHERE id=? AND member_id=?");
$up->bind_param("ii", $id, $member_id);
$up->execute();

/* 3) insert trainer notification */
$nt = $conn->prepare("
  INSERT INTO notifications (trainer_id, member_id, type, title, message, is_read)
  VALUES (?, ?, 'schedule_done', 'Schedule Completed', ?, 0)
");
$msg = "Member completed schedule: {$title}" . ($date ? " ({$date})" : "");
$nt->bind_param("iis", $trainer_id, $member_id, $msg);
$nt->execute();

echo json_encode(["ok"=>true]);