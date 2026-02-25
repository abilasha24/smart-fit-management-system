<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

$body = json_decode(file_get_contents("php://input"), true);
$id = (int)($body['id'] ?? 0);

if ($id <= 0) {
  echo json_encode(["ok"=>false, "message"=>"Invalid id"]);
  exit;
}

// detect columns
$existingCols = [];
$desc = $conn->query("DESCRIBE feedback");
while ($r = $desc->fetch_assoc()) $existingCols[] = $r['Field'];

$colIsRead = null;
$colStatus = null;
if (in_array("is_read", $existingCols, true)) $colIsRead = "is_read";
if (in_array("read", $existingCols, true)) $colIsRead = "read";
if (in_array("status", $existingCols, true)) $colStatus = "status";

if ($colIsRead) {
  $stmt = $conn->prepare("UPDATE feedback SET `$colIsRead`=1 WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  echo json_encode(["ok"=>true]);
  exit;
}

if ($colStatus) {
  $stmt = $conn->prepare("UPDATE feedback SET `$colStatus`='read' WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  echo json_encode(["ok"=>true]);
  exit;
}

echo json_encode(["ok"=>false, "message"=>"Mark read not supported (no status/is_read column)"]);