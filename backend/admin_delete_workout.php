<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "message" => "Not logged in"]);
  exit;
}
if ($_SESSION['role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok" => false, "message" => "Forbidden"]);
  exit;
}

require_once "db.php";

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
  http_response_code(422);
  echo json_encode(["ok" => false, "message" => "Invalid id"]);
  exit;
}

$stmt = $conn->prepare("DELETE FROM workouts WHERE id=?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Prepare failed"]);
  exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo json_encode(["ok" => true, "message" => "Deleted"]);
  } else {
    http_response_code(404);
    echo json_encode(["ok" => false, "message" => "Workout not found"]);
  }
} else {
  http_response_code(500);
  echo json_encode(["ok" => false, "message" => "Delete failed"]);
}

$stmt->close();
$conn->close();