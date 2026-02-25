<?php
header("Content-Type: application/json");
session_start();
require_once __DIR__ . "/db.php";

function respond($arr,$code=200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

if (empty($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
  respond(["ok"=>false,"message"=>"Unauthorized"], 401);
}

$sql = "SELECT id, code, monthly_price, duration_days, status 
        FROM plans 
        ORDER BY id ASC";

$res = $conn->query($sql);

if (!$res) {
  respond([
    "ok"=>false,
    "message"=>"Query failed",
    "error"=>$conn->error
  ],500);
}

$plans = [];
while($row = $res->fetch_assoc()){
  $plans[] = $row;
}

respond([
  "ok"=>true,
  "plans"=>$plans
]);