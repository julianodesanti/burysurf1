<?php
// Absolutely first thing - write to log
$logfile = __DIR__ . '/newsletter_send.log';
file_put_contents($logfile, "\n=== START " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

error_reporting(E_ALL);
ini_set('display_errors', '0');

try {
    file_put_contents($logfile, "1. Starting script\n", FILE_APPEND);
    
    ob_start();
    header('Content-Type: application/json; charset=utf-8');
    
    file_put_contents($logfile, "2. Headers set\n", FILE_APPEND);
    
    require_once __DIR__ . '/check_auth.php';
    file_put_contents($logfile, "3. check_auth loaded\n", FILE_APPEND);
    
    require_once __DIR__ . '/db_config.php';
    file_put_contents($logfile, "4. db_config loaded\n", FILE_APPEND);

    // Verify connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    file_put_contents($logfile, "5. Database connected\n", FILE_APPEND);

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

    file_put_contents($logfile, "6. Table verified\n", FILE_APPEND);

    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Novas fotos do dia no bUrY_+sUrF';
    $messageBody = isset($_POST['body']) ? trim($_POST['body']) : "Há novas fotos do dia no site. Visite para ver as atualizações.";

    if (empty($subject) || empty($messageBody)) {
        throw new Exception('Assunto e mensagem são obrigatórios');
    }

    file_put_contents($logfile, "7. Subject: " . $subject . "\n", FILE_APPEND);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    $res = $conn->query('SELECT email, unsub_token FROM newsletter_subscribers WHERE active = 1');
    
    if (!$res) {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    $sent = 0;
    $failed = 0;
    $count = 0;

    file_put_contents($logfile, "8. About to process emails\n", FILE_APPEND);

    while ($row = $res->fetch_assoc()) {
        $count++;
        $to = $row['email'];
        
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $failed++;
            file_put_contents($logfile, "   Invalid email: " . $to . "\n", FILE_APPEND);
            continue;
        }
        
        file_put_contents($logfile, "   Processing: " . $to . "\n", FILE_APPEND);
        
        $unsubscribe = $scheme . '://' . $host . '/api/newsletter_unsubscribe.php?token=' . urlencode($row['unsub_token']);
        $body = $messageBody . "\n\nPara cancelar a inscrição, clique aqui: " . $unsubscribe;

        // Try PHPMailer
        try {
            $phpMailerPath = __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
            
            if (!file_exists($phpMailerPath)) {
                file_put_contents($logfile, "   PHPMailer not found at: " . $phpMailerPath . "\n", FILE_APPEND);
                $failed++;
                continue;
            }

            require_once $phpMailerPath;
            require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
            require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
            
            file_put_contents($logfile, "   PHPMailer loaded\n", FILE_APPEND);

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = 'email-ssl.com.br';
            $mail->Port = 465;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_IMPLICIT;
            $mail->SMTPAuth = true;
            $mail->Username = 'publicidade@burysurf.com';
            $mail->Password = 'Afelicidadesoexistequandocompartilhada!10';
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom('publicidade@burysurf.com', 'bUrY_+sUrF');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(false);
            
            if ($mail->send()) {
                $sent++;
                file_put_contents($logfile, "   ✓ Sent to: " . $to . "\n", FILE_APPEND);
            } else {
                $failed++;
                file_put_contents($logfile, "   ✗ Failed to: " . $to . " - " . $mail->ErrorInfo . "\n", FILE_APPEND);
            }
        } catch (Exception $e) {
            $failed++;
            file_put_contents($logfile, "   Exception for " . $to . ": " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    file_put_contents($logfile, "9. Completed - Sent: " . $sent . ", Failed: " . $failed . ", Total: " . $count . "\n", FILE_APPEND);

    ob_end_clean();
    echo json_encode(['success' => true, 'sent' => $sent, 'failed' => $failed, 'total' => $count]);
    
} catch (Exception $e) {
    file_put_contents($logfile, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
file_put_contents($logfile, "=== END ===\n", FILE_APPEND);
exit;

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
    $smtpPort = 465;                        // Usually 587 for TLS or 465 for SSL
    $smtpUser = 'publicidade@burysurf.com';     // Change to your email
    $smtpPass = 'Afelicidadesoexistequandocompartilhada!10';        // Change to your app password
    $fromEmail = 'publicidade@burysurf.com';    // Change to your email
    $fromName = 'bUrY_+sUrF';

    // Load PHPMailer
    $phpMailerPath = __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    $smtpPath = __DIR__ . '/PHPMailer-master/src/SMTP.php';
    $exceptionPath = __DIR__ . '/PHPMailer-master/src/Exception.php';
    
    error_log('Checking PHPMailer files:', 3, $logfile);
    error_log('  PHPMailer: ' . (file_exists($phpMailerPath) ? 'OK' : 'NOT FOUND'), 3, $logfile);
    error_log('  SMTP: ' . (file_exists($smtpPath) ? 'OK' : 'NOT FOUND'), 3, $logfile);
    error_log('  Exception: ' . (file_exists($exceptionPath) ? 'OK' : 'NOT FOUND'), 3, $logfile);
    
    if (!file_exists($phpMailerPath)) {
        throw new Exception('PHPMailer.php not found at: ' . $phpMailerPath);
    }
    if (!file_exists($smtpPath)) {
        throw new Exception('SMTP.php not found at: ' . $smtpPath);
    }
    if (!file_exists($exceptionPath)) {
        throw new Exception('Exception.php not found at: ' . $exceptionPath);
    }

    require_once $phpMailerPath;
    error_log('PHPMailer.php loaded', 3, $logfile);
    
    require_once $smtpPath;
    error_log('SMTP.php loaded', 3, $logfile);
    
    require_once $exceptionPath;
    error_log('Exception.php loaded', 3, $logfile);

    error_log('All PHPMailer files loaded successfully', 3, $logfile);

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
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->Port = $smtpPort;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_IMPLICIT;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            
            // Set charset
            $mail->CharSet = 'UTF-8';
            
            // Set from and to
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            
            // Email content
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(false);
            
            // Send
            if ($mail->send()) {
                $sent++;
                error_log('Email sent successfully to: ' . $to, 3, $logfile);
            } else {
                $failed++;
                error_log('Email failed to send to: ' . $to . ' - ' . $mail->ErrorInfo, 3, $logfile);
            }
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $failed++;
            error_log('PHPMailer Exception for ' . $to . ': ' . $e->getMessage(), 3, $logfile);
        } catch (Exception $e) {
            $failed++;
            error_log('General Exception for ' . $to . ': ' . $e->getMessage(), 3, $logfile);
        }
    }

    error_log('Sent: ' . $sent . ', Failed: ' . $failed . ', Total: ' . $count, 3, $logfile);

    ob_end_clean();
    
    echo json_encode(['success' => true, 'sent' => $sent, 'failed' => $failed, 'total' => $count]);
    
} catch (Exception $e) {
    error_log('Exception caught: ' . $e->getMessage(), 3, $logfile);
    error_log('Stack trace: ' . $e->getTraceAsString(), 3, $logfile);
    
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Handle any remaining fatal errors
register_shutdown_function(function() use ($logfile) {
    $error = error_get_last();
    if ($error !== null && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        error_log('Fatal error: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'], 3, $logfile);
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $error['message']]);
    }
});

if (isset($conn)) {
    $conn->close();
}
exit;