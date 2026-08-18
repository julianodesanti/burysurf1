<?php
error_log("=== PHPMailer Test Start ===", 3, __DIR__ . '/phpmailer_test.log');

try {
    require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    error_log("PHPMailer loaded successfully", 3, __DIR__ . '/phpmailer_test.log');
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'email-ssl.com.br';
    $mail->SMTPAuth = true;
    $mail->Username = 'publicidade@burysurf.com';
    $mail->Password = 'Afelicidadesoexistequandocompartilhada!10';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_IMPLICIT;
    $mail->Port = 465;
    
    error_log("SMTP configured", 3, __DIR__ . '/phpmailer_test.log');
    
    $mail->setFrom('publicidade@burysurf.com', 'bUrY_+sUrF');
    $mail->addAddress('burysurftest@gmail.com');
    $mail->Subject = 'PHPMailer Test';
    $mail->Body = 'This is a test from PHPMailer';
    
    if ($mail->send()) {
        error_log("EMAIL SENT SUCCESSFULLY!", 3, __DIR__ . '/phpmailer_test.log');
        echo "Success!";
    } else {
        error_log("Send failed: " . $mail->ErrorInfo, 3, __DIR__ . '/phpmailer_test.log');
        echo "Failed: " . $mail->ErrorInfo;
    }
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage(), 3, __DIR__ . '/phpmailer_test.log');
    echo "Error: " . $e->getMessage();
}

error_log("=== Test End ===", 3, __DIR__ . '/phpmailer_test.log');
