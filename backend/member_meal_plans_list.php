<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

session_start();

/**
 * Expecting session like:
 * $_SESSION['user_id']
 * $_SESSION['role'] = 'member'
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
  http_response_code(401);
  echo json_encode(["ok"=>false, "message"=>"Unauthorized"]);
  exit;
}

if ($_SESSION['role'] !== 'member') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

$uid = (int)$_SESSION['user_id'];

require_once __DIR__ . '/db.php'; // <-- your DB connection file (must set $pdo OR $conn)

/**
 * Support both mysqli ($conn) and PDO ($pdo) without breaking.
 */
try {
  // ✅ If you use PDO
  if (isset($pdo) && $pdo instanceof PDO) {
    $stmt = $pdo->prepare("
      SELECT id, trainer_id, title, content, created_at
      FROM meal_plans
      ORDER BY id DESC
    ");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["ok"=>true, "plans"=>$plans]);
    exit;
  }

  // ✅ If you use mysqli
  if (isset($conn) && $conn instanceof mysqli) {
    $sql = "SELECT id, trainer_id, title, content, created_at FROM meal_plans ORDER BY id DESC";
    $res = $conn->query($sql);

    $plans = [];
    if ($res) {
      while($row = $res->fetch_assoc()){
        $plans[] = $row;
      }
    }
    echo json_encode(["ok"=>true, "plans"=>$plans]);
    exit;
  }

  // If neither connection exists
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"DB connection not found in db.php"]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Server error"]);
  exit;
}