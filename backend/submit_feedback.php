<?php
// backend/submit_feedback.php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

// member auth
$role = strtolower(trim($_SESSION["role"] ?? ""));
if (!isset($_SESSION["user_id"]) || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

if($_SERVER["REQUEST_METHOD"] !== "POST"){
  http_response_code(405);
  echo json_encode(["ok"=>false, "message"=>"Method not allowed"]);
  exit;
}

$user_id = (int)$_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

$subject = trim($data["subject"] ?? "");
$message = trim($data["message"] ?? "");
$rating  = isset($data["rating"]) ? (int)$data["rating"] : null;

if($subject === "" || strlen($subject) < 3){
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Subject must be at least 3 characters"]);
  exit;
}
if($message === "" || strlen($message) < 5){
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Message must be at least 5 characters"]);
  exit;
}
if($rating !== null && ($rating < 1 || $rating > 5)){
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Rating must be between 1 and 5"]);
  exit;
}

$sql = "INSERT INTO feedback (user_id, subject, message, rating) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["ok"=>false, "message"=>"SQL prepare failed", "error"=>$conn->error]);
  exit;
}

$stmt->bind_param("issi", $user_id, $subject, $message, $rating);

if(!$stmt->execute()){
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Failed to submit feedback"]);
  exit;
}

echo json_encode(["ok"=>true, "message"=>"Feedback submitted"]);
