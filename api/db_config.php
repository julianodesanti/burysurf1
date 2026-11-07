<?php

define('DB_HOST', '186.202.152.199');
define('DB_USERNAME', 'burysurf');
define('DB_PASSWORD', 'masntemOUTRO9!');
define('DB_NAME', 'burysurf');
define('DB_PORT', 3306);

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

if ($conn->connect_errno) {
    error_log('DB connect error: ' . $conn->connect_error);
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Database connection failed.']));
}

$conn->set_charset('utf8mb4');

// NOTE: no closing PHP tag to avoid accidental trailing output