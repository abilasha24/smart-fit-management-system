<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    http_response_code(401);
    echo json_encode(["ok"=>false]);
    exit;
}

require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM feedback WHERE id=?");
$stmt->bind_param("i", $id);

if($stmt->execute()){
    echo json_encode(["ok"=>true]);
}else{
    echo json_encode(["ok"=>false,"message"=>"Delete failed"]);
}