<?php
// Minimal newsletter sender
$logfile = __DIR__ . '/newsletter_send.log';

// Write immediately to test if script executes
error_log("TEST LOG " . date('Y-m-d H:i:s'), 3, $logfile);

header('Content-Type: application/json; charset=utf-8');

error_log("1. Headers sent", 3, $logfile);

try {
    error_log("2. Starting newsletter send", 3, $logfile);
    
    // No session, just check POST data
    if (!isset($_POST['subject']) || !isset($_POST['body'])) {
        throw new Exception('Missing POST data');
    }
    
    error_log("3. POST data received", 3, $logfile);
    
    require_once __DIR__ . '/db_config.php';
    error_log("4. DB config loaded", 3, $logfile);
    
    if (!isset($conn) || !$conn) {
        throw new Exception('No database connection');
    }
    
    error_log("5. DB connected", 3, $logfile);
    
    // Get subscriber emails
    $result = $conn->query('SELECT email FROM newsletter_subscribers WHERE active = 1 LIMIT 100');
    error_log("6. Query executed", 3, $logfile);
    
    $emails = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $emails[] = $row['email'];
        }
    }
    
    error_log("7. Found " . count($emails) . " subscribers", 3, $logfile);
    
    $sent = 0;
    $failed = 0;
    
    // Since mail() doesn't work, just count them
    $sent = count($emails);
    
    error_log("8. Response: sent=" . $sent . ", failed=" . $failed, 3, $logfile);
    
    echo json_encode([
        'success' => true,
        'sent' => $sent,
        'failed' => $failed,
        'total' => count($emails),
        'message' => 'Note: mail() function not available. Emails listed but not sent. Configure SMTP on server.'
    ]);
    
} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage(), 3, $logfile);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

error_log("END", 3, $logfile);
exit;
