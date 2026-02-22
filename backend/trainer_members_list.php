<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$res = $conn->query("SELECT id, first_name, last_name, email, plan, created_at 
                     FROM users 
                     WHERE role='member'
                     ORDER BY id DESC");

$rows = [];
while($row = $res->fetch_assoc()){
  $row['name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
  $rows[] = $row;
}

echo json_encode(["ok"=>true,"members"=>$rows]);
