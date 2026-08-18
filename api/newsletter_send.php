<?php
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

// Newsletter sender using PHPMailer + SMTP
$logfile = __DIR__ . '/newsletter_send.log';

// Log immediately
@file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] START\n", FILE_APPEND);

header('Content-Type: application/json; charset=utf-8');

try {
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] Loading PHPMailer\n", FILE_APPEND);
    
    // Load PHPMailer classes
    require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
    require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
    
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] PHPMailer classes loaded\n", FILE_APPEND);
    
    // Validate POST data
    if (!isset($_POST['subject']) || !isset($_POST['body'])) {
        throw new Exception('Missing subject or body in POST');
    }
    
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
    
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] Subject: " . substr($subject, 0, 40) . "...\n", FILE_APPEND);
    
    // Load database
    require_once __DIR__ . '/db_config.php';
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] Database config loaded\n", FILE_APPEND);
    
    if (!isset($conn)) {
        throw new Exception('Database connection object not set');
    }
    
    // Get subscriber emails
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] Fetching subscribers...\n", FILE_APPEND);
    
    $result = $conn->query('SELECT email FROM newsletter_subscribers WHERE active = 1');
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $email = trim($row['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $email;
        }
    }
    
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] Found " . count($emails) . " valid emails\n", FILE_APPEND);
    
    // SMTP Configuration
    $smtp_host = 'email-ssl.com.br';
    $smtp_port = 465;
    $smtp_user = 'publicidade@burysurf.com';
    $smtp_pass = 'Afelicidadesoexistequandocompartilhada!10';
    $from_email = 'publicidade@burysurf.com';
    $from_name = 'bUrY_+sUrF';
    
    $sent = 0;
    $failed = 0;
    
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] Starting email sending...\n", FILE_APPEND);
    
    foreach ($emails as $to_email) {
        @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] Sending to: " . $to_email . "\n", FILE_APPEND);
        
        try {
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $smtp_port;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->Timeout = 10;
            $mail->SMTPDebug = 0;
            
            // Message
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($to_email);
            $mail->Subject = $subject;
            $mail->isHTML(false);
            $mail->Body = $body;
            
            // Send
            if ($mail->send()) {
                $sent++;
                @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "]   ✓ SENT OK\n", FILE_APPEND);
            } else {
                $failed++;
                @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "]   ✗ Send failed: " . $mail->ErrorInfo . "\n", FILE_APPEND);
            }
            
        } catch (MailException $e) {
            $failed++;
            @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "]   ✗ Exception: " . $e->getMessage() . "\n", FILE_APPEND);
        } catch (Exception $e) {
            $failed++;
            @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "]   ✗ Exception: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
    
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] DONE - Sent: $sent, Failed: $failed\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'sent' => $sent,
        'failed' => $failed,
        'total' => count($emails)
    ]);
    
} catch (Exception $e) {
    @file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] FATAL ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

@file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] END\n", FILE_APPEND);
exit;
