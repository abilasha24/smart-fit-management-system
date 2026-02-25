<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(["ok"=>false,"message"=>"Forbidden"]);
    exit;
}

require_once __DIR__ . "/db.php";

// Read JSON body
$body = json_decode(file_get_contents("php://input"), true);
$id = isset($body['id']) ? (int)$body['id'] : 0;

if ($id <= 0) {
    echo json_encode(["ok"=>false,"message"=>"Invalid id"]);
    exit;
}

// Delete workout
$stmt = $conn->prepare("DELETE FROM workouts WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(["ok"=>true]);