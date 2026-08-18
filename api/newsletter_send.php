<?php
ob_start();

$logfile = __DIR__ . '/newsletter_send.log';

try {
    header('Content-Type: application/json; charset=utf-8');
    
    require_once __DIR__ . '/check_auth.php';
    require_once __DIR__ . '/db_config.php';

    error_log('Newsletter send started', 3, $logfile);

    // Verify connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    error_log('Database connected', 3, $logfile);

    // ensure table exists
    $tableSql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        unsub_token VARCHAR(128) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($tableSql)) {
        throw new Exception('Failed to create table: ' . $conn->error);
    }

    error_log('Table verified', 3, $logfile);

    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Novas fotos do dia no bUrY_+sUrF';
    $messageBody = isset($_POST['body']) ? trim($_POST['body']) : "Há novas fotos do dia no site. Visite para ver as atualizações.";

    if (empty($subject) || empty($messageBody)) {
        throw new Exception('Assunto e mensagem são obrigatórios');
    }

    error_log('Subject: ' . $subject, 3, $logfile);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    $res = $conn->query('SELECT email, unsub_token FROM newsletter_subscribers WHERE active = 1');
    
    if (!$res) {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    $sent = 0;
    $failed = 0;
    $count = 0;

    // SMTP Configuration - UPDATE THESE WITH YOUR EMAIL SETTINGS
    $smtpHost = 'email-ssl.com.br';           // Change this to your SMTP host
    $smtpPort = 587;                        // Usually 587 for TLS or 465 for SSL
    $smtpUser = 'publicidade@burysurf.com';     // Change to your email
    $smtpPass = 'Afelicidadesoexistequandocompartilhada!10';        // Change to your app password
    $fromEmail = 'publicidade@burysurf.com';    // Change to your email
    $fromName = 'bUrY_+sUrF';

    while ($row = $res->fetch_assoc()) {
        $count++;
        $to = $row['email'];
        
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $failed++;
            error_log('Invalid email: ' . $to, 3, $logfile);
            continue;
        }
        
        $unsubscribe = $scheme . '://' . $host . '/api/newsletter_unsubscribe.php?token=' . urlencode($row['unsub_token']);
        $body = $messageBody . "\n\nPara cancelar a inscrição, clique aqui: " . $unsubscribe;

        try {
            // Try using mail() function first (if available)
            if (function_exists('mail')) {
                $headers = array(
                    'From: ' . $fromName . ' <' . $fromEmail . '>',
                    'Reply-To: ' . $fromEmail,
                    'MIME-Version: 1.0',
                    'Content-Type: text/plain; charset=UTF-8'
                );
                $ok = @mail($to, $subject, $body, implode("\r\n", $headers));
            } else {
                // Fallback: Just skip for now - need SMTP configuration
                error_log('mail() not available and SMTP not configured for: ' . $to, 3, $logfile);
                $failed++;
                continue;
            }
            
            if ($ok) {
                $sent++;
                error_log('Email sent to: ' . $to, 3, $logfile);
            } else {
                $failed++;
                error_log('Email failed to: ' . $to, 3, $logfile);
            }
        } catch (Exception $e) {
            $failed++;
            error_log('Email exception for ' . $to . ': ' . $e->getMessage(), 3, $logfile);
        }
    }

    error_log('Sent: ' . $sent . ', Failed: ' . $failed . ', Total: ' . $count, 3, $logfile);

    ob_end_clean();
    
    if ($sent > 0) {
        echo json_encode(['success' => true, 'sent' => $sent, 'failed' => $failed, 'total' => $count]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Configure SMTP settings in api/newsletter_send.php (lines 59-64) or enable mail() function']);
    }
    
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage(), 3, $logfile);
    error_log('Trace: ' . $e->getTraceAsString(), 3, $logfile);
    
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
exit;