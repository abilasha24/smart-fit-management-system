<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db.php"; // provides $conn (mysqli)

function respond($arr, $code = 200) {
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

try {
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(["status" => "error", "message" => "Invalid request"], 405);
  }

  // =========================
  // 1) Collect fields
  // =========================
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName  = trim($_POST['lastName'] ?? '');
  $username  = trim($_POST['username'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $phone     = trim($_POST['phone'] ?? '');
  $password  = $_POST['password'] ?? '';
  $role      = $_POST['role'] ?? 'member';

  // Step2 profile (optional)
  $dob         = trim($_POST['dob'] ?? '');
  $gender      = trim($_POST['gender'] ?? '');
  $height      = trim($_POST['height'] ?? '');
  $weight      = trim($_POST['weight'] ?? '');
  $fitnessLevel= trim($_POST['fitnessLevel'] ?? '');
  $goal        = trim($_POST['goal'] ?? '');

  // Plan + payment info (frontend sends only plan now; we default others)
  $plan         = trim($_POST['plan'] ?? 'premium');           // basic/premium/pro
  $billingCycle = trim($_POST['billing_cycle'] ?? 'monthly');  // monthly/yearly
  $paymentMethod= trim($_POST['payment_method'] ?? 'card');    // card/bank/mobile/free

  // =========================
  // 2) Basic validation
  // =========================
  if ($firstName === '' || $lastName === '' || $email === '' || $phone === '' || $password === '') {
    respond(["status" => "error", "message" => "Missing required fields"]);
  }

  // Email validate
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(["status" => "error", "message" => "Invalid email format"]);
  }

  // Phone validate (Sri Lanka) -> allow +94xxxxxxxxx or 0xxxxxxxxx or 9 digits without prefix
  $rawPhone = preg_replace('/\s+/', '', $phone);
  if (!preg_match('/^(?:\+94|0)?7\d{8}$|^(?:\+94|0)?\d{9}$/', $rawPhone)) {
    // keep it mild: allow common formats
    // you can tighten later to mobile-only: /^(?:\+94|0)?7\d{8}$/
    // respond if you want strict:
    // respond(["status"=>"error","message"=>"Phone number must be Sri Lankan"]);
  }

  // Username generate if missing
  if ($username === '') {
    $base = strtolower($firstName . "_" . $lastName);
    $base = preg_replace('/[^a-z0-9_]/', '', $base);
    if ($base === '') $base = "user";
    $username = $base . rand(100, 999);
  }

  // Normalize plan
  $plan = strtolower($plan);
  if (!in_array($plan, ["basic","premium","pro"], true)) $plan = "premium";

  // Normalize cycle/method
  $billingCycle = strtolower($billingCycle);
  if (!in_array($billingCycle, ["monthly","yearly"], true)) $billingCycle = "monthly";

  $paymentMethod = strtolower($paymentMethod);
  if (!in_array($paymentMethod, ["card","bank","mobile","free"], true)) $paymentMethod = "card";

  // =========================
  // 3) Duplicate email check
  // =========================
  $check = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
  if (!$check) respond(["status" => "error", "message" => "Prepare failed (email check): ".$conn->error]);

  $check->bind_param("s", $email);
  $check->execute();
  $res = $check->get_result();
  if ($res && $res->num_rows > 0) {
    respond(["status" => "error", "message" => "Email already registered"]);
  }

  // =========================
  // 4) Get amount from plans table (BEST)
  // =========================
  $amount = 0.00;
  $planStmt = $conn->prepare("SELECT monthly_price FROM plans WHERE code=? LIMIT 1");
  if ($planStmt) {
    $planStmt->bind_param("s", $plan);
    $planStmt->execute();
    $planRes = $planStmt->get_result();
    if ($planRes && $row = $planRes->fetch_assoc()) {
      $amount = (float)$row["monthly_price"];
    }
    $planStmt->close();
  } else {
    // fallback mapping if table not available
    if ($plan === "basic") $amount = 0.00;
    if ($plan === "premium") $amount = 2500.00;
    if ($plan === "pro") $amount = 5000.00;
  }

  // If basic => free
  if ($plan === "basic") {
    $amount = 0.00;
    $paymentMethod = "free";
  }

  // =========================
  // 5) Transaction (insert user + profile + payment)
  // =========================
  $conn->begin_transaction();

  // Hash password
  $hash = password_hash($password, PASSWORD_BCRYPT);

  // Insert users
  $sql = "INSERT INTO users (role, username, first_name, last_name, email, phone, password)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    $conn->rollback();
    respond(["status" => "error", "message" => "Prepare failed (insert user): " . $conn->error]);
  }

  $stmt->bind_param("sssssss", $role, $username, $firstName, $lastName, $email, $phone, $hash);

  if (!$stmt->execute()) {
    $conn->rollback();
    respond(["status" => "error", "message" => "Insert failed: " . $stmt->error]);
  }

  $userId = $stmt->insert_id;
  $stmt->close();

  // Insert/Update member_profiles (only if any profile field exists)
  $hasProfile = ($dob !== '' || $gender !== '' || $height !== '' || $weight !== '' || $fitnessLevel !== '' || $goal !== '');
  if ($hasProfile) {
    // if row exists -> update, else insert
    $exists = $conn->prepare("SELECT user_id FROM member_profiles WHERE user_id=? LIMIT 1");
    if ($exists) {
      $exists->bind_param("i", $userId);
      $exists->execute();
      $existsRes = $exists->get_result();
      $rowExists = ($existsRes && $existsRes->num_rows > 0);
      $exists->close();

      if ($rowExists) {
        $upd = $conn->prepare("UPDATE member_profiles SET dob=?, gender=?, height=?, weight=?, fitness_level=?, goal=?, updated_at=NOW() WHERE user_id=?");
        if (!$upd) { $conn->rollback(); respond(["status"=>"error","message"=>"Profile update prepare failed: ".$conn->error]); }
        $upd->bind_param("ssisssi", $dob, $gender, $height, $weight, $fitnessLevel, $goal, $userId);
        if (!$upd->execute()) { $conn->rollback(); respond(["status"=>"error","message"=>"Profile update failed: ".$upd->error]); }
        $upd->close();
      } else {
        $ins = $conn->prepare("INSERT INTO member_profiles (user_id, dob, gender, height, weight, fitness_level, goal, updated_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$ins) { $conn->rollback(); respond(["status"=>"error","message"=>"Profile insert prepare failed: ".$conn->error]); }
        $ins->bind_param("ississs", $userId, $dob, $gender, $height, $weight, $fitnessLevel, $goal);
        if (!$ins->execute()) { $conn->rollback(); respond(["status"=>"error","message"=>"Profile insert failed: ".$ins->error]); }
        $ins->close();
      }
    }
  }

  // Insert payments (for success page / subscription page)
  $pay = $conn->prepare("INSERT INTO payments (user_id, user_email, plan, billing_cycle, payment_method, amount, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, NOW())");
  if (!$pay) {
    $conn->rollback();
    respond(["status" => "error", "message" => "Payment prepare failed: " . $conn->error]);
  }

  $pay->bind_param("issssd", $userId, $email, $plan, $billingCycle, $paymentMethod, $amount);

  if (!$pay->execute()) {
    $conn->rollback();
    respond(["status" => "error", "message" => "Payment insert failed: " . $pay->error]);
  }
  $pay->close();

  // Optional: welcome notification
  $note = $conn->prepare("INSERT INTO notifications (user_id, title, message, is_read, created_at)
                          VALUES (?, 'Welcome to Smart Fit', 'Your member account has been created successfully!', 0, NOW())");
  if ($note) {
    $note->bind_param("i", $userId);
    $note->execute();
    $note->close();
  }

  $conn->commit();

  respond([
    "status" => "success",
    "message" => "Registered successfully",
    "user_id" => $userId,
    "username" => $username,
    "plan" => $plan,
    "amount" => $amount,
    "billing_cycle" => $billingCycle,
    "payment_method" => $paymentMethod
  ]);

} catch (Throwable $e) {
  if (isset($conn) && $conn instanceof mysqli) {
    $conn->rollback();
  }
  respond(["status" => "error", "message" => "Server error: " . $e->getMessage()], 500);
}
