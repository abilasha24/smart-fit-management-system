<?php
// backend/get_progress.php
session_start();
require_once __DIR__ . "/db.php";
header("Content-Type: application/json; charset=UTF-8");

function respond($arr, $code = 200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

// member auth
$role = strtolower(trim($_SESSION["role"] ?? ""));
if (empty($_SESSION["user_id"]) || $role !== "member") {
  respond(["ok"=>false, "message"=>"Login required"], 401);
}
$user_id = (int)$_SESSION["user_id"];

/* ---------- helpers: detect tables/columns safely ---------- */
function tableExists($conn, $table){
  $sql = "SELECT 1 FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
          LIMIT 1";
  $st = $conn->prepare($sql);
  if(!$st) return false;
  $st->bind_param("s", $table);
  $st->execute();
  $res = $st->get_result();
  $ok = ($res && $res->num_rows > 0);
  $st->close();
  return $ok;
}

function columnsOf($conn, $table){
  $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
  $st = $conn->prepare($sql);
  if(!$st) return [];
  $st->bind_param("s", $table);
  $st->execute();
  $res = $st->get_result();
  $cols = [];
  while($r = $res->fetch_assoc()){
    $cols[] = $r["COLUMN_NAME"];
  }
  $st->close();
  return $cols;
}

function hasCol($cols, $name){
  return in_array($name, $cols, true);
}

/* ---------- 1) choose the correct workouts-tracking table ---------- */
$uwTable = null;
if (tableExists($conn, "user_workouts")) $uwTable = "user_workouts";
elseif (tableExists($conn, "member_workouts")) $uwTable = "member_workouts";

if(!$uwTable){
  respond(["ok"=>false, "message"=>"Tracking table not found (user_workouts/member_workouts)"], 500);
}

$uwCols = columnsOf($conn, $uwTable);

/* ---------- 2) detect key column names ---------- */
$userCol =
  hasCol($uwCols, "user_id") ? "user_id" :
  (hasCol($uwCols, "member_id") ? "member_id" : null);

$workoutCol =
  hasCol($uwCols, "workout_id") ? "workout_id" :
  (hasCol($uwCols, "workouts_id") ? "workouts_id" : null);

$statusCol = hasCol($uwCols, "status") ? "status" : null;

// timestamps
$createdCol =
  hasCol($uwCols, "created_at") ? "created_at" :
  (hasCol($uwCols, "started_at") ? "started_at" : null);

$completedCol =
  hasCol($uwCols, "completed_at") ? "completed_at" :
  (hasCol($uwCols, "completed_on") ? "completed_on" : null);

// basic must-have checks
if(!$userCol){
  respond(["ok"=>false, "message"=>"Cannot find user column in ".$uwTable." (user_id/member_id)"], 500);
}
if(!$workoutCol){
  respond(["ok"=>false, "message"=>"Cannot find workout column in ".$uwTable." (workout_id)"], 500);
}
if(!$createdCol && !$completedCol){
  // still can work without dates, but daily chart needs date
  // we will fallback to TODAY for grouping to avoid crash
}

/* ---------- 3) statusCounts (started/completed) ---------- */
$statusCounts = ["started"=>0, "completed"=>0];

if($statusCol){
  // use status column
  $sql1 = "SELECT LOWER($statusCol) AS s, COUNT(*) AS cnt
           FROM `$uwTable`
           WHERE `$userCol`=?
           GROUP BY LOWER($statusCol)";
  $stmt1 = $conn->prepare($sql1);
  if(!$stmt1){
    respond(["ok"=>false,"message"=>"SQL prepare failed (statusCounts)","error"=>$conn->error], 500);
  }
  $stmt1->bind_param("i", $user_id);
  $stmt1->execute();
  $res1 = $stmt1->get_result();
  while($r = $res1->fetch_assoc()){
    $s = $r["s"] ?? "";
    if(isset($statusCounts[$s])) $statusCounts[$s] = (int)$r["cnt"];
  }
  $stmt1->close();

} elseif($completedCol) {
  // no status column -> infer from completed_at
  $sql1 = "SELECT
            SUM(CASE WHEN `$completedCol` IS NULL THEN 1 ELSE 0 END) AS started_cnt,
            SUM(CASE WHEN `$completedCol` IS NOT NULL THEN 1 ELSE 0 END) AS completed_cnt
          FROM `$uwTable`
          WHERE `$userCol`=?";
  $stmt1 = $conn->prepare($sql1);
  if(!$stmt1){
    respond(["ok"=>false,"message"=>"SQL prepare failed (statusCounts infer)","error"=>$conn->error], 500);
  }
  $stmt1->bind_param("i", $user_id);
  $stmt1->execute();
  $r = $stmt1->get_result()->fetch_assoc();
  $statusCounts["started"] = (int)($r["started_cnt"] ?? 0);
  $statusCounts["completed"] = (int)($r["completed_cnt"] ?? 0);
  $stmt1->close();
} else {
  // worst fallback
  $statusCounts["started"] = 0;
  $statusCounts["completed"] = 0;
}

/* ---------- 4) daily totals (completed only) ---------- */
/*
  Needs:
  - workout join to workouts table
  - date column (completed_at preferred, else created_at)
*/
$daily = [];

// Check workouts table exists
if(!tableExists($conn, "workouts")){
  // still return counts, but no daily chart
  respond(["ok"=>true, "statusCounts"=>$statusCounts, "daily"=>$daily]);
}

// Decide date expression
$dateExpr = "CURDATE()"; // fallback
if($completedCol && $createdCol){
  $dateExpr = "DATE(COALESCE(uw.`$completedCol`, uw.`$createdCol`))";
} elseif($completedCol){
  $dateExpr = "DATE(uw.`$completedCol`)";
} elseif($createdCol){
  $dateExpr = "DATE(uw.`$createdCol`)";
}

// completed condition
$completedWhere = "";
if($statusCol){
  $completedWhere = "AND uw.`$statusCol`='completed'";
} elseif($completedCol){
  $completedWhere = "AND uw.`$completedCol` IS NOT NULL";
}

// Build query
$sql2 = "
  SELECT
    $dateExpr AS d,
    SUM(COALESCE(w.duration_min,0)) AS total_minutes,
    SUM(COALESCE(w.calories,0)) AS total_calories,
    COUNT(*) AS completed_count
  FROM `$uwTable` uw
  JOIN `workouts` w ON uw.`$workoutCol` = w.`id`
  WHERE uw.`$userCol`=? $completedWhere
  GROUP BY d
  ORDER BY d ASC
";

$stmt2 = $conn->prepare($sql2);
if(!$stmt2){
  // Return counts even if daily fails
  respond([
    "ok"=>true,
    "statusCounts"=>$statusCounts,
    "daily"=>[],
    "warning"=>"Daily query failed (check columns/dates)",
    "error"=>$conn->error
  ]);
}

$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

while($row = $res2->fetch_assoc()){
  $daily[] = [
    "date" => $row["d"],
    "minutes" => (int)($row["total_minutes"] ?? 0),
    "calories" => (int)($row["total_calories"] ?? 0),
    "completed_count" => (int)($row["completed_count"] ?? 0)
  ];
}
$stmt2->close();

respond([
  "ok"=>true,
  "statusCounts"=>$statusCounts,
  "daily"=>$daily,
  "meta"=>[
    "table"=>$uwTable,
    "user_col"=>$userCol,
    "workout_col"=>$workoutCol,
    "status_col"=>$statusCol,
    "created_col"=>$createdCol,
    "completed_col"=>$completedCol
  ]
]);
