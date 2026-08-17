<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid email']);
    exit;
}

// ensure table exists
$tableSql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    unsub_token VARCHAR(128) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($tableSql);

$token = bin2hex(random_bytes(16));
$now = date('Y-m-d H:i:s');

// insert or update
$stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, unsub_token, active, created_at) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE unsub_token = VALUES(unsub_token), active = 1, created_at = VALUES(created_at)");
$stmt->bind_param('sss', $email, $token, $now);

if ($stmt->execute()) {
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Subscribed', 'token' => $token]);
} else {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$stmt->close();
$conn->close();
exit;
