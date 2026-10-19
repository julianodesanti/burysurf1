<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/db_config.php';

function newsletterResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    newsletterResponse(405, ['success' => false, 'error' => 'Método não permitido.']);
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!is_string($csrfToken) || empty($_SESSION['newsletter_csrf']) || !hash_equals($_SESSION['newsletter_csrf'], $csrfToken)) {
    newsletterResponse(403, ['success' => false, 'error' => 'A sessão expirou. Atualize a página e tente novamente.']);
}

$submittedSubject = $_POST['subject'] ?? '';
$submittedBody = $_POST['body'] ?? '';
if (!is_string($submittedSubject) || !is_string($submittedBody)) {
    newsletterResponse(400, ['success' => false, 'error' => 'Informe um assunto válido e um corpo de até 100.000 bytes.']);
}

$subject = trim($submittedSubject);
$body = trim($submittedBody);
if ($subject === '' || strlen($subject) > 200 || preg_match('/[\r\n]/', $subject) ||
    $body === '' || strlen($body) > 100000) {
    newsletterResponse(400, ['success' => false, 'error' => 'Informe um assunto válido e um corpo de até 100.000 bytes.']);
}

$fromEmail = getenv('NEWSLETTER_FROM_EMAIL') ?: 'contato@burysurf.com';
if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL) || !function_exists('mail')) {
    error_log('Newsletter mail is unavailable or NEWSLETTER_FROM_EMAIL is invalid.');
    newsletterResponse(500, ['success' => false, 'error' => 'O envio de e-mails não está configurado corretamente no servidor.']);
}

$result = $conn->query('SELECT id, email FROM newsletter_subscribers WHERE confirmed_at IS NOT NULL ORDER BY id');
if (!$result) {
    error_log('Newsletter recipient query failed: ' . $conn->error);
    newsletterResponse(500, ['success' => false, 'error' => 'Não foi possível carregar a lista de inscritos.']);
}

if ($result->num_rows === 0) {
    $result->free();
    newsletterResponse(400, ['success' => false, 'error' => 'Não há inscritos para receber a newsletter.']);
}

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
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

$sent = 0;
$failed = 0;
while ($recipient = $result->fetch_assoc()) {
    if (@mail($recipient['email'], $encodedSubject, $body, implode("\r\n", $headerLines))) {
        $sent++;
    } else {
        $failed++;
        error_log('Newsletter delivery failed for subscriber id ' . (int) $recipient['id']);
    }
}
$result->free();

if ($failed > 0) {
    newsletterResponse($sent > 0 ? 207 : 502, [
        'success' => false,
        'sent' => $sent,
        'failed' => $failed,
        'error' => $sent > 0
            ? 'Envio parcial: alguns e-mails não foram aceitos pelo servidor.'
            : 'O servidor não aceitou nenhum e-mail para envio.'
    ]);
}

newsletterResponse(200, [
    'success' => true,
    'sent' => $sent,
    'message' => 'Newsletter aceita para envio a todos os inscritos (' . $sent . ').'
]);
