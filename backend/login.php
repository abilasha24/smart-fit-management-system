<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php'; // gives $conn (mysqli)

try {
    // ✅ Accept JSON OR normal POST
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";

    $email = "";
    $password = "";
    $role = "member";

    if (stripos($contentType, "application/json") !== false) {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $role = trim($data['role'] ?? 'member');
    } else {
        // fallback (if frontend sends FormData / normal POST)
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'member');
    }

    if ($email === '' || $password === '') {
        http_response_code(400);
        echo json_encode(["ok" => false, "message" => "Email & password required"]);
        exit;
    }

    // ✅ Get user by email (also fetch username column)
    $stmt = $conn->prepare("SELECT id, role, username, first_name, last_name, email, password 
                            FROM users 
                            WHERE email=? 
                            LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res || $res->num_rows === 0) {
        http_response_code(401);
        echo json_encode(["ok" => false, "message" => "Invalid credentials"]);
        exit;
    }

    $user = $res->fetch_assoc();

    // ✅ Role check (only if your login UI requires role selection)
    if ($role !== '' && $role !== $user['role']) {
        http_response_code(401);
        echo json_encode(["ok" => false, "message" => "Role mismatch. Select correct role."]);
        exit;
    }

    // ✅ Verify password (DB has bcrypt hash like $2y$10$...)
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["ok" => false, "message" => "Invalid credentials"]);
        exit;
    }

    // ✅ Session set
    $fullName = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // store both
    $_SESSION['username'] = $user['username'];     // actual username column
    $_SESSION['full_name'] = $fullName;            // friendly display

    echo json_encode([
        "ok" => true,
        "user_id" => (int)$user['id'],
        "role" => $user['role'],
        "username" => $user['username'],
        "full_name" => $fullName
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Server error: " . $e->getMessage()]);
    exit;
}
