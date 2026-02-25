<?php
header("Content-Type: application/json");
session_start();
require_once __DIR__ . "/db.php";

function respond($arr, $code=200){ http_response_code($code); echo json_encode($arr); exit; }

if (empty($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
  respond(["ok"=>false, "message"=>"Unauthorized"], 401);
}

$id = (int)($_POST["id"] ?? 0);
if ($id <= 0) respond(["ok"=>false, "message"=>"Invalid id"], 400);

$stmt = $conn->prepare("DELETE FROM plans WHERE id=?");
if (!$stmt) respond(["ok"=>false, "message"=>"Server error"], 500);
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$stmt->close();

respond(["ok"=>$ok, "message"=>$ok?"Deleted":"Delete failed"], $ok?200:500);