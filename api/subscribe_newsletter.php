<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_config.php';
date_default_timezone_set('America/Sao_Paulo');

function subscriptionResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    subscriptionResponse(405, ['success' => false, 'error' => 'Método não permitido.']);
}

$submittedEmail = $_POST['email'] ?? '';
if (!is_string($submittedEmail)) {
    subscriptionResponse(400, ['success' => false, 'error' => 'Informe um endereço de e-mail válido.']);
}

$email = strtolower(trim($submittedEmail));
if ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    subscriptionResponse(400, ['success' => false, 'error' => 'Informe um endereço de e-mail válido.']);
}

$lookup = $conn->prepare('SELECT confirmed_at FROM newsletter_subscribers WHERE email = ?');
if (!$lookup) {
    error_log('Newsletter subscription lookup prepare failed: ' . $conn->error);
    subscriptionResponse(500, ['success' => false, 'error' => 'Não foi possível iniciar a confirmação. Tente novamente mais tarde.']);
}
$lookup->bind_param('s', $email);
if (!$lookup->execute()) {
    error_log('Newsletter subscription lookup failed: ' . $lookup->error);
    $lookup->close();
    subscriptionResponse(500, ['success' => false, 'error' => 'Não foi possível iniciar a confirmação. Tente novamente mais tarde.']);
}
$existing = $lookup->get_result()->fetch_assoc();
$lookup->close();

if ($existing && $existing['confirmed_at'] !== null) {
    subscriptionResponse(200, ['success' => true, 'message' => 'Se a inscrição ainda não foi confirmada, enviamos um link para o seu e-mail. Verifique sua caixa de entrada.']);
}

$fromEmail = getenv('NEWSLETTER_FROM_EMAIL') ?: 'contato@burysurf.com';
if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL) || !function_exists('mail')) {
    error_log('Newsletter confirmation mail is unavailable or NEWSLETTER_FROM_EMAIL is invalid.');
    subscriptionResponse(500, ['success' => false, 'error' => 'O envio de e-mails não está configurado corretamente no servidor.']);
}

$confirmationToken = bin2hex(random_bytes(32));
$confirmationTokenHash = hash('sha256', $confirmationToken);
$confirmationExpiresAt = date('Y-m-d H:i:s', time() + 48 * 60 * 60);
$unsubscribeToken = bin2hex(random_bytes(16));
$createdAt = date('Y-m-d H:i:s');
$stmt = $conn->prepare(
    'INSERT INTO newsletter_subscribers (email, created_at, unsub_token, confirmation_token_hash, confirmation_expires_at)
     VALUES (?, ?, ?, ?, ?)'
);
if (!$stmt) {
    error_log('Newsletter subscription prepare failed: ' . $conn->error);
    subscriptionResponse(500, ['success' => false, 'error' => 'Não foi possível iniciar a confirmação. Tente novamente mais tarde.']);
}

$stmt->bind_param('sssss', $email, $createdAt, $unsubscribeToken, $confirmationTokenHash, $confirmationExpiresAt);
if ($stmt->execute()) {
    $stmt->close();
} elseif ($stmt->errno === 1062) {
    $stmt->close();
    $update = $conn->prepare(
        'UPDATE newsletter_subscribers
         SET confirmation_token_hash = ?, confirmation_expires_at = ?
         WHERE email = ? AND confirmed_at IS NULL'
    );
    if (!$update) {
        error_log('Newsletter confirmation refresh prepare failed: ' . $conn->error);
        subscriptionResponse(500, ['success' => false, 'error' => 'Não foi possível iniciar a confirmação. Tente novamente mais tarde.']);
    }
    $update->bind_param('sss', $confirmationTokenHash, $confirmationExpiresAt, $email);
    if (!$update->execute()) {
        error_log('Newsletter confirmation refresh failed: ' . $update->error);
        $update->close();
        subscriptionResponse(500, ['success' => false, 'error' => 'Não foi possível iniciar a confirmação. Tente novamente mais tarde.']);
    }
    $shouldSendEmail = $update->affected_rows === 1;
    $update->close();
} else {
    $errorCode = $stmt->errno;
    $stmt->close();
    error_log('Newsletter subscription insert failed with database error ' . $errorCode);
    subscriptionResponse(500, ['success' => false, 'error' => 'Não foi possível iniciar a confirmação. Tente novamente mais tarde.']);
}

if (isset($shouldSendEmail) && !$shouldSendEmail) {
    subscriptionResponse(200, ['success' => true, 'message' => 'Se a inscrição ainda não foi confirmada, enviamos um link para o seu e-mail. Verifique sua caixa de entrada.']);
}

$confirmationUrl = 'https://www.burysurf.com/api/confirm_newsletter.php?token=' . rawurlencode($confirmationToken);
$subject = 'Confirme sua inscrição na newsletter bUrY_+sUrF';
$message = "Recebemos um pedido para inscrever este e-mail na newsletter bUrY_+sUrF.\n\n"
    . "Para confirmar, abra o link abaixo e clique em \"Confirmar inscrição\":\n"
    . $confirmationUrl . "\n\n"
    . "O link expira em 48 horas. Se você não solicitou a inscrição, ignore esta mensagem.\n";
$headers = [
    'From' => 'bUrY_+sUrF <' . $fromEmail . '>',
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/plain; charset=UTF-8',
    'Content-Transfer-Encoding' => '8bit'
];
$headerLines = [];
foreach ($headers as $name => $value) {
    $headerLines[] = $name . ': ' . $value;
}

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
if (!mail($email, $encodedSubject, $message, implode("\r\n", $headerLines))) {
    error_log('Newsletter confirmation delivery failed.');
    subscriptionResponse(502, ['success' => false, 'error' => 'Não foi possível enviar o e-mail de confirmação. Tente novamente mais tarde.']);
}

subscriptionResponse(200, ['success' => true, 'message' => 'Se a inscrição ainda não foi confirmada, enviamos um link para o seu e-mail. Verifique sua caixa de entrada.']);
