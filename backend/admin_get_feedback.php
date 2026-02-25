<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(["ok"=>false, "message"=>"Forbidden"]);
  exit;
}

require_once __DIR__ . "/db.php";

/**
 * Find first existing column from candidates.
 */
function pick_col(array $existing, array $candidates) {
  foreach ($candidates as $c) {
    if (in_array($c, $existing, true)) return $c;
  }
  return null;
}

$existingCols = [];
$desc = $conn->query("DESCRIBE feedback");
if (!$desc) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"feedback table not found"]);
  exit;
}
while ($r = $desc->fetch_assoc()) {
  $existingCols[] = $r['Field'];
}

// common possible column names
$colId        = pick_col($existingCols, ["id", "feedback_id"]);
$colName      = pick_col($existingCols, ["name", "full_name", "user_name"]);
$colEmail     = pick_col($existingCols, ["email", "user_email"]);
$colPhone     = pick_col($existingCols, ["phone", "user_phone"]);
$colSubject   = pick_col($existingCols, ["subject", "title"]);
$colMessage   = pick_col($existingCols, ["message", "feedback", "content", "comment", "description"]);
$colCreatedAt = pick_col($existingCols, ["created_at", "created", "submitted_at", "date", "timestamp"]);
$colStatus    = pick_col($existingCols, ["status"]);         // e.g., unread/read
$colIsRead    = pick_col($existingCols, ["is_read", "read"]); // 0/1

if (!$colId || !$colMessage) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"feedback table missing required columns (id/message)"]);
  exit;
}

$q = trim($_GET['q'] ?? '');
$filter = trim($_GET['filter'] ?? 'all'); // all | unread | read

$where = [];
$params = [];
$types = "";

// Search
if ($q !== '') {
  $like = "%".$q."%";
  $parts = [];
  // only add fields that exist
  if ($colName)    $parts[] = "f.`$colName` LIKE ?";
  if ($colEmail)   $parts[] = "f.`$colEmail` LIKE ?";
  if ($colPhone)   $parts[] = "f.`$colPhone` LIKE ?";
  if ($colSubject) $parts[] = "f.`$colSubject` LIKE ?";
  $parts[] = "f.`$colMessage` LIKE ?";

  $where[] = "(" . implode(" OR ", $parts) . ")";
  // bind same like for each part
  $bindCount = count($parts);
  for ($i=0; $i<$bindCount; $i++){
    $params[] = $like;
    $types .= "s";
  }
}

// Read/Unread filter (only if table supports it)
$supportsRead = ($colStatus !== null) || ($colIsRead !== null);

if ($supportsRead && ($filter === 'unread' || $filter === 'read')) {
  if ($colIsRead) {
    $where[] = "f.`$colIsRead` = ?";
    $params[] = ($filter === 'read') ? 1 : 0;
    $types .= "i";
  } else if ($colStatus) {
    // assume status values could be 'read'/'unread' or '0'/'1'
    if ($filter === 'read') {
      $where[] = "(f.`$colStatus`='read' OR f.`$colStatus`='1' OR f.`$colStatus`=1)";
    } else {
      $where[] = "(f.`$colStatus`='unread' OR f.`$colStatus`='0' OR f.`$colStatus`=0 OR f.`$colStatus` IS NULL)";
    }
  }
}

// Build SELECT with aliases (so frontend always gets same keys)
$select = [];
$select[] = "f.`$colId` AS id";
$select[] = ($colName      ? "f.`$colName` AS name" : "'' AS name");
$select[] = ($colEmail     ? "f.`$colEmail` AS email" : "'' AS email");
$select[] = ($colPhone     ? "f.`$colPhone` AS phone" : "'' AS phone");
$select[] = ($colSubject   ? "f.`$colSubject` AS subject" : "'' AS subject");
$select[] = "f.`$colMessage` AS message";
$select[] = ($colCreatedAt ? "f.`$colCreatedAt` AS created_at" : "'' AS created_at");

// expose a unified read flag
if ($supportsRead) {
  if ($colIsRead) {
    $select[] = "f.`$colIsRead` AS is_read";
  } else {
    // status -> is_read
    $select[] = "CASE WHEN (f.`$colStatus`='read' OR f.`$colStatus`='1' OR f.`$colStatus`=1) THEN 1 ELSE 0 END AS is_read";
  }
} else {
  $select[] = "0 AS is_read";
}

$sql = "SELECT " . implode(", ", $select) . " FROM feedback f";
if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC LIMIT 300";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "message"=>"Prepare failed"]);
  exit;
}

if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$rows = [];
$unreadCount = 0;

while ($row = $res->fetch_assoc()) {
  $row['is_read'] = (int)($row['is_read'] ?? 0);
  if ($row['is_read'] === 0) $unreadCount++;
  $rows[] = $row;
}

echo json_encode([
  "ok" => true,
  "supports_mark_read" => $supportsRead,
  "unread_count" => $unreadCount,
  "feedback" => $rows
]);