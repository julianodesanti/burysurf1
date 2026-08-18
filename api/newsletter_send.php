<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/check_auth.php';
    require_once __DIR__ . '/db_config.php';

    // ensure table exists
    $tableSql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        unsub_token VARCHAR(128) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($tableSql);

    $subject = trim($_POST['subject'] ?? 'Novas fotos do dia no bUrY_+sUrF');
    $messageBody = trim($_POST['body'] ?? "Há novas fotos do dia no site. Visite para ver as atualizações.");

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    $res = $conn->query('SELECT email, unsub_token FROM newsletter_subscribers WHERE active = 1');
    $sent = 0;
    $failed = 0;

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $to = $row['email'];
            $unsubscribe = $scheme . '://' . $host . '/api/newsletter_unsubscribe.php?token=' . urlencode($row['unsub_token']);

            $body = $messageBody . "\n\nPara cancelar a inscrição, clique aqui: " . $unsubscribe;

            $headers = [];
            $fromAddr = 'no-reply@' . $host;
            $headers[] = 'From: bUrY_+sUrF <' . $fromAddr . '>';
            $headers[] = 'Reply-To: no-reply@' . $host;
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            $ok = mail($to, $subject, $body, implode("\r\n", $headers));
            if ($ok) $sent++; else $failed++;
        }
    } else {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    ob_end_clean();
    echo json_encode(['success' => true, 'sent' => $sent, 'failed' => $failed]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
if (isset($conn)) $conn->close();
exit;