<?php
// backend/logout.php
session_start();
session_unset();
session_destroy();

// go back to login page
header("Location: ../login.html");
exit;
