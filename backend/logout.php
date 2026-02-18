<?php
session_start();

// session clear
$_SESSION = [];
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
session_destroy();

/* ✅ If browser direct open -> redirect to login */
if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false) {
  header("Location: ../login.html"); // SMART_FIT/login.html
  exit;
}

/* ✅ If fetch/AJAX -> return JSON */
header("Content-Type: application/json");
echo json_encode(["ok" => true]);

exit;
