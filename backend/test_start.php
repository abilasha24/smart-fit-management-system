<?php
// backend/test_start.php
$payload = json_encode(["workout_id" => 1]);

$ch = curl_init("http://localhost/SMART_FIT/backend/start_workout.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_COOKIEFILE, ""); // send current session cookie if browser keeps it (may vary)
echo curl_exec($ch);
