<?php
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db_config.php';

function renderConfirmationPage(string $title, string $message, ?string $token = null, bool $isError = false): void
{
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $messageClass = $isError ? 'error' : 'success';
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . $safeTitle . ' - bUrY_+sUrF</title>'
        . '<style>body{margin:0;padding:24px;background:#000;color:#e7eee8;font:16px/1.5 "Segoe UI",sans-serif}'
        . 'main{max-width:520px;margin:10vh auto;padding:24px;border:1px solid #00d64f;border-radius:8px}'
        . 'h1{font-size:1.5rem}.success{color:#45e879}.error{color:#ff7373}'
        . 'button,a{display:inline-block;margin-top:16px;padding:10px 16px;border:0;border-radius:4px;'
        . 'background:#00d64f;color:#07100a;font:inherit;font-weight:bold;text-decoration:none;cursor:pointer}</style>'
        . '</head><body><main><h1>' . $safeTitle . '</h1><p class="' . $messageClass . '">' . $safeMessage . '</p>';
    if ($token !== null) {
        echo '<form method="post"><input type="hidden" name="token" value="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
            . '"><button type="submit">Confirmar inscrição</button></form>';
    } else {
        echo '<a href="https://www.burysurf.com/">Voltar ao site</a>';
    }
    echo '</main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: GET, POST');
    renderConfirmationPage('Método não permitido', 'Abra o link recebido por e-mail para confirmar sua inscrição.', null, true);
}

$submittedToken = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['token'] ?? '')
    : ($_GET['token'] ?? '');
if (!is_string($submittedToken) || !preg_match('/^[a-f0-9]{64}$/', $submittedToken)) {
    http_response_code(400);
    renderConfirmationPage('Link inválido', 'Solicite novamente a inscrição na newsletter para receber um novo link.', null, true);
}

$tokenHash = hash('sha256', $submittedToken);
$now = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lookup = $conn->prepare(
        'SELECT id FROM newsletter_subscribers
         WHERE confirmation_token_hash = ? AND confirmed_at IS NULL AND confirmation_expires_at > ?'
    );
    if (!$lookup) {
        error_log('Newsletter confirmation lookup prepare failed: ' . $conn->error);
        http_response_code(500);
        renderConfirmationPage('Não foi possível confirmar', 'Tente novamente mais tarde.', null, true);
    }
    $lookup->bind_param('ss', $tokenHash, $now);
    if (!$lookup->execute()) {
        error_log('Newsletter confirmation lookup failed: ' . $lookup->error);
        $lookup->close();
        http_response_code(500);
        renderConfirmationPage('Não foi possível confirmar', 'Tente novamente mais tarde.', null, true);
    }
    $subscriber = $lookup->get_result()->fetch_assoc();
    $lookup->close();

    if (!$subscriber) {
        renderConfirmationPage('Link expirado ou inválido', 'Solicite novamente a inscrição na newsletter para receber um novo link.', null, true);
    }

    renderConfirmationPage('Confirme sua inscrição', 'Clique abaixo para confirmar que deseja receber a newsletter bUrY_+sUrF.', $submittedToken);
}

$confirm = $conn->prepare(
    'UPDATE newsletter_subscribers
     SET confirmed_at = ?, confirmation_token_hash = NULL, confirmation_expires_at = NULL
     WHERE confirmation_token_hash = ? AND confirmed_at IS NULL AND confirmation_expires_at > ?'
);
if (!$confirm) {
    error_log('Newsletter confirmation update prepare failed: ' . $conn->error);
    http_response_code(500);
    renderConfirmationPage('Não foi possível confirmar', 'Tente novamente mais tarde.', null, true);
}
$confirm->bind_param('sss', $now, $tokenHash, $now);
if (!$confirm->execute()) {
    error_log('Newsletter confirmation update failed: ' . $confirm->error);
    $confirm->close();
    http_response_code(500);
    renderConfirmationPage('Não foi possível confirmar', 'Tente novamente mais tarde.', null, true);
}
$confirmed = $confirm->affected_rows === 1;
$confirm->close();

if (!$confirmed) {
    renderConfirmationPage('Link expirado ou já utilizado', 'Se você já confirmou, sua inscrição está ativa. Caso contrário, solicite um novo link.', null, true);
}

renderConfirmationPage('Inscrição confirmada', 'Obrigado! Seu e-mail foi confirmado e passará a receber a newsletter.', null);
