<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

if (!isset($conn) || !($conn instanceof mysqli)) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"DB connection object missing"]);
  exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "message"=>"Invalid session user_id"]);
  exit;
}

function prepFail(mysqli $conn, string $where) {
  http_response_code(500);
  echo json_encode([
    "ok" => false,
    "message" => "Prepare failed: {$where}",
    "err" => $conn->error
  ]);
  exit;
}

function execFail(mysqli_stmt $stmt, string $where) {
  http_response_code(500);
  echo json_encode([
    "ok" => false,
    "message" => "Execute failed: {$where}",
    "err" => $stmt->error
  ]);
  exit;
}

/* -----------------------------------------------------------
   1) Get user's plan
----------------------------------------------------------- */
$plan = "basic";

$stmt = $conn->prepare("SELECT plan FROM users WHERE id=? LIMIT 1");
if (!$stmt) prepFail($conn, "users.plan");

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) execFail($stmt, "users.plan");

$res = $stmt->get_result();
if ($res && ($row = $res->fetch_assoc())) {
  $plan = trim($row['plan'] ?? "basic");
}
$stmt->close();

$plan = strtolower($plan);

/* -----------------------------------------------------------
   2) Get latest BMI (bmi_records.bmi_value)
----------------------------------------------------------- */
$bmi = null;

$stmt = $conn->prepare("SELECT bmi_value AS bmi FROM bmi_records WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
if (!$stmt) prepFail($conn, "bmi_records.latest");

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) execFail($stmt, "bmi_records.latest");

$res = $stmt->get_result();
if ($res && ($row = $res->fetch_assoc())) {
  $bmi = is_numeric($row['bmi']) ? (float)$row['bmi'] : null;
}
$stmt->close();

/* -----------------------------------------------------------
   3) NEW AI Enhancement: Progress-based intelligence
   completed workouts count from user_workouts
----------------------------------------------------------- */
$completedCount = 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_workouts WHERE user_id=? AND status='completed'");
if (!$stmt) prepFail($conn, "user_workouts.completed_count");

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) execFail($stmt, "user_workouts.completed_count");

$res = $stmt->get_result();
if ($res && ($row = $res->fetch_assoc())) {
  $completedCount = (int)($row['total'] ?? 0);
}
$stmt->close();

/* -----------------------------------------------------------
   4) Rule-based AI recommendation (BMI-based)
----------------------------------------------------------- */
$category = "Balanced Fitness";
$tip = "Maintain routine: mix cardio + strength.";

if ($bmi !== null) {
  if ($bmi < 18.5) {
    $category = "Strength Gain";
    $tip = "Focus on strength + calorie surplus meals.";
  } elseif ($bmi <= 24.9) {
    $category = "Balanced Fitness";
    $tip = "Great range! keep balanced training weekly.";
  } else {
    $category = "Fat Burn / Cardio";
    $tip = "Add cardio + keep calories controlled.";
  }
} else {
  $category = "Balanced Fitness";
  $tip = "Add BMI record to get more accurate suggestions.";
}

/* -----------------------------------------------------------
   5) Level selection (Plan + Progress based)
   - Plan decides max potential levels
   - Progress decides current recommended levels
----------------------------------------------------------- */

// plan max levels
$planMaxLevels = ($plan === "basic")
  ? ["beginner"]
  : ["beginner", "intermediate", "advanced"];

// progress-based recommended levels
if ($completedCount <= 2) {
  $levels = ["beginner"];
} elseif ($completedCount <= 10) {
  $levels = ["beginner", "intermediate"];
} else {
  $levels = ["beginner", "intermediate", "advanced"];
}

// ensure levels do not exceed plan limits
$levels = array_values(array_intersect($levels, $planMaxLevels));
if (empty($levels)) {
  // fallback safe
  $levels = ["beginner"];
}

/* -----------------------------------------------------------
   6) Pick recommended workouts from workouts table
----------------------------------------------------------- */
$keyword = "";
if ($category === "Fat Burn / Cardio") $keyword = "cardio";
if ($category === "Strength Gain")     $keyword = "strength";
if ($category === "Balanced Fitness")  $keyword = "full";

$placeholders = implode(",", array_fill(0, count($levels), "?"));

$sql = "SELECT id, title, level, duration_min, calories, youtube_url
        FROM workouts
        WHERE level IN ($placeholders)
        ORDER BY (LOWER(title) LIKE ?) DESC, created_at DESC
        LIMIT 3";

$stmt = $conn->prepare($sql);
if (!$stmt) prepFail($conn, "workouts.recommendation");

$like = "%" . strtolower($keyword) . "%";
$types = str_repeat("s", count($levels)) . "s";
$params = array_merge($levels, [$like]);

$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) execFail($stmt, "workouts.recommendation");

$res = $stmt->get_result();
$workouts = [];
if ($res) {
  while ($w = $res->fetch_assoc()) $workouts[] = $w;
}
$stmt->close();

/* -----------------------------------------------------------
   Response
----------------------------------------------------------- */
echo json_encode([
  "ok" => true,
  "plan" => $plan,
  "bmi" => $bmi,
  "completed_workouts" => $completedCount,   // ✅ NEW: show to confirm AI logic
  "levels_used" => $levels,                  // ✅ NEW: for debugging/demo
  "category" => $category,
  "tip" => $tip,
  "workouts" => $workouts
]);
exit;