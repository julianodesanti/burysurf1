<?php
// Newsletter sending via SMTP
$logfile = __DIR__ . '/newsletter_send.log';
file_put_contents($logfile, "\n=== START " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

header('Content-Type: application/json; charset=utf-8');

try {
    file_put_contents($logfile, "1. Script started\n", FILE_APPEND);
    
    require_once __DIR__ . '/check_auth.php';
    require_once __DIR__ . '/db_config.php';
    
    file_put_contents($logfile, "2. Includes loaded\n", FILE_APPEND);
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Create newsletter table if needed
    $tableSql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        unsub_token VARCHAR(128),
        active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    
    if (!$conn->query($tableSql)) {
        throw new Exception('Table creation failed: ' . $conn->error);
    }

    file_put_contents($logfile, "3. Table verified\n", FILE_APPEND);

    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Newsletter';
    $body = isset($_POST['body']) ? trim($_POST['body']) : '';

    if (!$subject || !$body) {
        throw new Exception('Subject and body required');
    }

    file_put_contents($logfile, "4. Getting emails from database\n", FILE_APPEND);
    
    $result = $conn->query('SELECT email FROM newsletter_subscribers WHERE active = 1');
    $emails = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $emails[] = $row['email'];
            }
        }
    }

    file_put_contents($logfile, "5. Found " . count($emails) . " valid emails\n", FILE_APPEND);

    $sent = 0;
    $failed = 0;
    
    $from_email = 'publicidade@burysurf.com';
    $from_name = 'bUrY_+sUrF';

    foreach ($emails as $to_email) {
        file_put_contents($logfile, "   Sending to: " . $to_email . "\n", FILE_APPEND);
        
        try {
            // Try mail() function first
            if (function_exists('mail')) {
                $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                if (@mail($to_email, $subject, $body, $headers)) {
                    $sent++;
                    file_put_contents($logfile, "   ✓ mail() OK\n", FILE_APPEND);
                } else {
                    $failed++;
                    file_put_contents($logfile, "   ✗ mail() failed\n", FILE_APPEND);
                }
            } else {
                file_put_contents($logfile, "   ✗ mail() not available\n", FILE_APPEND);
                $failed++;
            }
        } catch (Exception $e) {
            $failed++;
            file_put_contents($logfile, "   ✗ Exception: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    file_put_contents($logfile, "6. Complete - Sent: $sent, Failed: $failed\n", FILE_APPEND);

    echo json_encode([
        'success' => true,
        'sent' => $sent,
        'failed' => $failed,
        'total' => count($emails)
    ]);

} catch (Exception $e) {
    file_put_contents($logfile, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    @$conn->close();
}

file_put_contents($logfile, "=== END ===\n", FILE_APPEND);
exit;
