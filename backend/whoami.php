<?php
session_start();
header('Content-Type: application/json');

echo json_encode([
  "logged_in" => $_SESSION['logged_in'] ?? false,
  "user_id" => $_SESSION['user_id'] ?? null,
  "email" => $_SESSION['email'] ?? null,
  "username" => $_SESSION['username'] ?? null,
  "role" => $_SESSION['role'] ?? null
]);
