<?php
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

mysqli_report(MYSQLI_REPORT_OFF);
error_reporting(0);
ini_set('display_errors', 0);

function hasCol($cols, $name){
  return in_array($name, $cols, true);
}

try {
  // ---- 1) Detect columns in users table
  $cols = [];
  $cRes = $conn->query("SHOW COLUMNS FROM users");
  if ($cRes) {
    while ($r = $cRes->fetch_assoc()) $cols[] = $r["Field"];
  }

  // Build safest member display expression
  // Priority: name > (first_name+last_name) > username > email > fallback
  if (hasCol($cols, "name")) {
    $memberExpr = "u.name";
  } elseif (hasCol($cols, "first_name") || hasCol($cols, "last_name")) {
    $fn = hasCol($cols, "first_name") ? "IFNULL(u.first_name,'')" : "''";
    $ln = hasCol($cols, "last_name")  ? "IFNULL(u.last_name,'')"  : "''";
    $memberExpr = "TRIM(CONCAT($fn,' ',$ln))";
  } elseif (hasCol($cols, "username")) {
    $memberExpr = "u.username";
  } elseif (hasCol($cols, "email")) {
    $memberExpr = "u.email";
  } else {
    $memberExpr = "CONCAT('User #', uw.user_id)";
  }

  // ---- 2) Query recent assigned
  $sql = "
    SELECT
      uw.id,
      uw.user_id,
      uw.workout_id,
      uw.status,
      uw.assigned_at,
      uw.created_at,
      $memberExpr AS member_name,
      w.title AS workout_title
    FROM user_workouts uw
    JOIN users u ON u.id = uw.user_id
    JOIN workouts w ON w.id = uw.workout_id
    ORDER BY uw.id DESC
    LIMIT 10
  ";

  $res = $conn->query($sql);
  if (!$res) {
    // Debug mode: show exact SQL error when you open with ?debug=1
    if (isset($_GET["debug"])) {
      echo json_encode(["ok"=>false, "message"=>"SQL Error", "error"=>$conn->error, "sql"=>$sql]);
      exit;
    }
    http_response_code(500);
    echo json_encode(["ok"=>false,"message"=>"Server error"]);
    exit;
  }

  $items = [];
  while ($row = $res->fetch_assoc()) $items[] = $row;

  echo json_encode(["ok"=>true, "items"=>$items]);

} catch (Throwable $e) {
  // Debug mode: show exception
  if (isset($_GET["debug"])) {
    echo json_encode(["ok"=>false, "message"=>"Exception", "error"=>$e->getMessage()]);
    exit;
  }
  http_response_code(500);
  echo json_encode(["ok"=>false,"message"=>"Server error"]);
}