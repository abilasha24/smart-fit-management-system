<?php
header('Content-Type: application/json');
session_start();

// ✅ Admin guard (உங்க existing session structure-க்கு adjust பண்ணலாம்)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(["ok"=>false, "message"=>"Unauthorized"]);
  exit;
}

require_once __DIR__ . "/db.php"; // <- உங்கள் DB connect file path

$trainerId = isset($_POST['trainer_id']) ? intval($_POST['trainer_id']) : 0;
$block     = isset($_POST['block']) ? intval($_POST['block']) : null; // 1=block, 0=unblock

if ($trainerId <= 0 || ($block !== 0 && $block !== 1)) {
  echo json_encode(["ok"=>false, "message"=>"Invalid input"]);
  exit;
}

// ✅ Only trainers should be affected
$sql = "UPDATE users SET is_blocked = ? WHERE id = ? AND role = 'trainer'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $block, $trainerId);

if ($stmt->execute()) {
  echo json_encode(["ok"=>true, "message"=> $block ? "Trainer blocked" : "Trainer activated"]);
} else {
  echo json_encode(["ok"=>false, "message"=>"DB update failed"]);
}