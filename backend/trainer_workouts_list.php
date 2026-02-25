<?php
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";
error_reporting(0); ini_set('display_errors',0);

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
  echo json_encode(["ok"=>false,"message"=>"Not logged in"]);
  exit;
}
if (strtolower($_SESSION["role"] ?? "") !== "trainer") {
  echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
  exit;
}

$q = trim($_GET["q"] ?? "");
$level = trim($_GET["level"] ?? ""); // beginner/intermediate/advanced optional

$sql = "SELECT id, title, level, duration_min, calories, youtube_url FROM workouts WHERE 1=1";
$params = [];
$types = "";

// search
if ($q !== "") {
  $sql .= " AND title LIKE ?";
  $params[] = "%{$q}%";
  $types .= "s";
}
if ($level !== "" && in_array(strtolower($level), ["beginner","intermediate","advanced"])) {
  $sql .= " AND level = ?";
  $params[] = strtolower($level);
  $types .= "s";
}

$sql .= " ORDER BY id DESC LIMIT 200";

$stmt = $conn->prepare($sql);
if(!empty($params)){
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()) $items[] = $row;

echo json_encode(["ok"=>true,"items"=>$items]);
exit;