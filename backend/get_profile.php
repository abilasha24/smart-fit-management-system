<?php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

$role = strtolower(trim($_SESSION["role"] ?? ""));
$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($user_id <= 0 || $role !== "member") {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Login required"]);
  exit;
}

/**
 * ✅ users table-ல் username column இல்லை.
 * ✅ so display "username" as CONCAT(first_name, last_name)
 */
$sql = "SELECT CONCAT(first_name, ' ', last_name) AS username, email
        FROM users
        WHERE id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
if(!$stmt){
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Prepare failed", "error"=>$conn->error]);
  exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

/* get_result() works only if mysqlnd is enabled.
   If your server doesn't support it, switch to bind_result method.
*/
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;

if(!$user){
  http_response_code(404);
  echo json_encode(["ok"=>false, "message"=>"User not found"]);
  exit;
}

echo json_encode(["ok"=>true, "user"=>$user]);
exit;