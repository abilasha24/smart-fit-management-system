<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'trainer') {
  http_response_code(403);
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . '/db.php';

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
  echo json_encode(["ok"=>false,"message"=>"Invalid user_id"]);
  exit;
}

$u = $conn->prepare("SELECT id, first_name, last_name, email FROM users WHERE id=? AND role='member' LIMIT 1");
$u->bind_param("i", $user_id);
$u->execute();
$ur = $u->get_result()->fetch_assoc();
if(!$ur){
  echo json_encode(["ok"=>false,"message"=>"Member not found"]);
  exit;
}

$sql = "
  SELECT
    uw.id,
    uw.status,
    uw.created_at,
    uw.completed_at,
    w.title,
    w.level
  FROM user_workouts uw
  JOIN workouts w ON w.id = uw.workout_id
  WHERE uw.user_id = ?
  ORDER BY uw.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($r = $res->fetch_assoc()){
  $items[] = [
    "id" => (int)$r["id"],
    "title" => $r["title"],
    "level" => $r["level"],
    "status" => $r["status"],
    "created_at" => $r["created_at"],
    "completed_at" => $r["completed_at"]
  ];
}

echo json_encode([
  "ok"=>true,
  "member"=>[
    "id" => (int)$ur["id"],
    "name" => trim(($ur["first_name"]??"")." ".($ur["last_name"]??"")),
    "email" => $ur["email"]
  ],
  "items"=>$items
]);