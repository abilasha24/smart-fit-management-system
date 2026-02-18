<?php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode(["ok"=>false,"message"=>"Method not allowed"]);
  exit;
}


$role = strtolower(trim($_SESSION["role"] ?? ""));
$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($user_id <= 0 || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false,"message"=>"Login required"]);
  exit;
}

// ✅ accept both FormData + JSON
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");
$rating  = $_POST["rating"] ?? null;

if ($subject === "" || $message === "") {
  echo json_encode(["ok"=>false,"message"=>"Subject and message required"]);
  exit;
}

$ratingInt = null;
if ($rating !== null && $rating !== "") {
  $ratingInt = (int)$rating;
  if ($ratingInt < 1 || $ratingInt > 5) $ratingInt = null;
}

$sql = "INSERT INTO feedback (user_id, subject, message, rating) VALUES (?,?,?,?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  echo json_encode(["ok"=>false,"message"=>"SQL prepare failed","error"=>$conn->error]);
  exit;
}

$stmt->bind_param("issi", $user_id, $subject, $message, $ratingInt);

if ($stmt->execute()) {
  echo json_encode(["ok"=>true,"message"=>"Feedback submitted ✅"]);
} else {
  echo json_encode(["ok"=>false,"message"=>"Insert failed","error"=>$stmt->error]);
}
