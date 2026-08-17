<?php
// Simple unsubscribe endpoint: GET ?token=...
require_once __DIR__ . '/db_config.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    http_response_code(400);
    echo 'Invalid unsubscribe token.';
    exit;
}

$tableSql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    unsub_token VARCHAR(128) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($tableSql);

$stmt = $conn->prepare('UPDATE newsletter_subscribers SET active = 0 WHERE unsub_token = ?');
$stmt->bind_param('s', $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Unsubscribed</title><link rel="stylesheet" href="/css/style.css"></head><body style="background:#000;color:#fff;font-family:Inconsolata,monospace;padding:20px;"><h2>Você foi removido da nossa lista de e-mails.</h2><p>Se isso foi um engano, você pode assinar novamente em <a href="/">site</a>.</p></body></html>';
} else {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Unsubscribe</title></head><body style="background:#000;color:#fff;font-family:Inconsolata,monospace;padding:20px;"><h2>Token inválido ou já cancelado.</h2><p>Se você ainda recebe e-mails, contate o suporte.</p></body></html>';
}

$stmt->close();
$conn->close();
exit;
