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
    $smtpPort = 465;                        // Usually 587 for TLS or 465 for SSL
    $smtpUser = 'publicidade@burysurf.com';     // Change to your email
    $smtpPass = 'Afelicidadesoexistequandocompartilhada!10';        // Change to your app password
    $fromEmail = 'publicidade@burysurf.com';    // Change to your email
    $fromName = 'bUrY_+sUrF';

    // Load PHPMailer
    $phpMailerPath = __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    $smtpPath = __DIR__ . '/PHPMailer-master/src/SMTP.php';
    $exceptionPath = __DIR__ . '/PHPMailer-master/src/Exception.php';
    
    if (!file_exists($phpMailerPath) || !file_exists($smtpPath) || !file_exists($exceptionPath)) {
        throw new Exception('PHPMailer library not found. Required files: ' . $phpMailerPath . ', ' . $smtpPath . ', ' . $exceptionPath);
    }

    require_once $phpMailerPath;
    require_once $smtpPath;
    require_once $exceptionPath;

    error_log('PHPMailer loaded successfully', 3, $logfile);

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