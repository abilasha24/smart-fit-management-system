<?php
session_start();
header('Content-Type: application/json');

// Not logged in
if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    echo json_encode([
        "logged_in" => false
    ]);
    exit;
}

// Pick the best display name
$displayName = "Member";

if (!empty($_SESSION['name'])) {
    $displayName = trim($_SESSION['name']);
} elseif (!empty($_SESSION['full_name'])) {
    $displayName = trim($_SESSION['full_name']);
} elseif (!empty($_SESSION['email'])) {
    $displayName = $_SESSION['email'];
}

echo json_encode([
    "logged_in" => true,
    "user_id"   => $_SESSION['user_id'],
    "email"     => $_SESSION['email'] ?? null,
    "role"      => $_SESSION['role'] ?? "member",
    "name"      => $displayName
]);
