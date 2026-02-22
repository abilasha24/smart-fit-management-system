<?php
// backend/db.php
mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "smart_fit";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
  http_response_code(500);
  die("DB connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
