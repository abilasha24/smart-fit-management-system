<?php
header("Content-Type: application/json");
require_once("db.php");

$first = trim($_POST["firstName"] ?? "");
$last  = trim($_POST["lastName"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$pass  = $_POST["password"] ?? "";

if ($first=="" || $last=="" || $email=="" || $phone=="" || $pass=="") {
  echo json_encode(["status"=>"error","message"=>"All required fields must be filled"]);
  exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$role = "member";

$stmt = $conn->prepare(
  "INSERT INTO users (role, first_name, last_name, email, phone, password)
   VALUES (?,?,?,?,?,?)"
);

$stmt->bind_param("ssssss", $role, $first, $last, $email, $phone, $hash);

if ($stmt->execute()) {
  echo json_encode(["status"=>"success"]);
} else {
  echo json_encode(["status"=>"error","message"=>$stmt->error]);
}
