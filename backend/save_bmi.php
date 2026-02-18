<?php
session_start();
header("Content-Type: application/json");
require_once "db.php";

function respond($arr, $code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

$role = strtolower($_SESSION["role"] ?? "");
if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"]) || $role !== "member") {
  respond(["ok"=>false, "message"=>"Login required"], 401);
}

$input = json_decode(file_get_contents("php://input"), true);
$height_cm = (float)($input["height_cm"] ?? 0);
$weight_kg = (float)($input["weight_kg"] ?? 0);

if ($height_cm <= 0 || $weight_kg <= 0) {
  respond(["ok"=>false, "message"=>"Height and weight required"], 400);
}
if ($height_cm < 50 || $height_cm > 250) {
  respond(["ok"=>false, "message"=>"Height must be between 50 and 250 cm"], 400);
}
if ($weight_kg < 10 || $weight_kg > 300) {
  respond(["ok"=>false, "message"=>"Weight must be between 10 and 300 kg"], 400);
}

$h_m = $height_cm / 100.0;
$bmi = $weight_kg / ($h_m * $h_m);
$bmi = round($bmi, 2);

// category
$category = "Normal";
if ($bmi < 18.5) $category = "Underweight";
elseif ($bmi < 25) $category = "Normal";
elseif ($bmi < 30) $category = "Overweight";
else $category = "Obese";

$user_id = (int)$_SESSION["user_id"];

$st = $conn->prepare("INSERT INTO bmi_records (user_id, height_cm, weight_kg, bmi_value, category) VALUES (?,?,?,?,?)");
if(!$st) respond(["ok"=>false,"message"=>"Prepare failed","error"=>$conn->error], 500);

$st->bind_param("iddds", $user_id, $height_cm, $weight_kg, $bmi, $category);
$st->execute();
$st->close();

respond([
  "ok"=>true,
  "bmi"=>$bmi,
  "category"=>$category
]);
