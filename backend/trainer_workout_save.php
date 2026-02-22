<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if(!is_array($data)){
  echo json_encode(["ok"=>false,"message"=>"Invalid JSON"]);
  exit;
}

$id = (int)($data["id"] ?? 0);
$title = trim($data["title"] ?? "");
$level = trim($data["level"] ?? "beginner");
$duration = (int)($data["duration_min"] ?? 0);
$calories = (int)($data["calories"] ?? 0);
$youtube = trim($data["youtube_url"] ?? "");

if($title === "") { echo json_encode(["ok"=>false,"message"=>"Title required"]); exit; }
if($duration <= 0) { echo json_encode(["ok"=>false,"message"=>"Duration must be > 0"]); exit; }

$validLevels = ["beginner","intermediate","advanced"];
if(!in_array($level, $validLevels, true)) $level = "beginner";

if($id > 0){
  $stmt = $conn->prepare("UPDATE workouts SET title=?, level=?, duration_min=?, calories=?, youtube_url=? WHERE id=?");
  if(!$stmt){ echo json_encode(["ok"=>false,"message"=>"SQL prepare failed: ".$conn->error]); exit; }
  $stmt->bind_param("ssissi", $title, $level, $duration, $calories, $youtube, $id);
  $ok = $stmt->execute();
  if(!$ok){ echo json_encode(["ok"=>false,"message"=>"Update failed: ".$stmt->error]); exit; }
  echo json_encode(["ok"=>true,"mode"=>"update"]);
  exit;
}

$stmt = $conn->prepare("INSERT INTO workouts (title, level, duration_min, calories, youtube_url) VALUES (?,?,?,?,?)");
if(!$stmt){ echo json_encode(["ok"=>false,"message"=>"SQL prepare failed: ".$conn->error]); exit; }
$stmt->bind_param("ssiss", $title, $level, $duration, $calories, $youtube);
$ok = $stmt->execute();
if(!$ok){ echo json_encode(["ok"=>false,"message"=>"Insert failed: ".$stmt->error]); exit; }

echo json_encode(["ok"=>true,"mode"=>"insert","id"=>$conn->insert_id]);