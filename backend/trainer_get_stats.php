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

// Only members assigned by THIS trainer
$q1 = $conn->prepare("SELECT COUNT(DISTINCT user_id) c FROM user_workouts WHERE trainer_id=?");
$q1->bind_param("i",$trainer_id);
$q1->execute();
$total_members = (int)($q1->get_result()->fetch_assoc()["c"] ?? 0);
$q1->close();

// Active assignments (assigned + started) by THIS trainer
$q2 = $conn->prepare("SELECT COUNT(*) c FROM user_workouts WHERE trainer_id=? AND status IN ('assigned','started')");
$q2->bind_param("i",$trainer_id);
$q2->execute();
$active_assignments = (int)($q2->get_result()->fetch_assoc()["c"] ?? 0);
$q2->close();

// Completed by THIS trainer
$q3 = $conn->prepare("SELECT COUNT(*) c FROM user_workouts WHERE trainer_id=? AND status='completed'");
$q3->bind_param("i",$trainer_id);
$q3->execute();
$completed_workouts = (int)($q3->get_result()->fetch_assoc()["c"] ?? 0);
$q3->close();

// Total workouts in system
$workouts = (int)($conn->query("SELECT COUNT(*) c FROM workouts")->fetch_assoc()["c"] ?? 0);

echo json_encode([
  "ok"=>true,
  "total_members"=>$total_members,
  "active_assignments"=>$active_assignments,
  "completed_workouts"=>$completed_workouts,
  "total_workouts"=>$workouts
]);