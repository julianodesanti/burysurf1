<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit;
}

$submittedEmail = $_POST['email'] ?? '';
if (!is_string($submittedEmail)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Informe um endereço de e-mail válido.']);
    exit;
}

$email = strtolower(trim($submittedEmail));
if ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Informe um endereço de e-mail válido.']);
    exit;
}

$unsubscribeToken = bin2hex(random_bytes(32));
$stmt = $conn->prepare('INSERT INTO newsletter_subscribers (email, unsub_token) VALUES (?, ?)');
if (!$stmt) {
    error_log('Newsletter subscription prepare failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Não foi possível concluir sua inscrição. Tente novamente mais tarde.']);
    exit;
}

$stmt->bind_param('ss', $email, $unsubscribeToken);
if ($stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Inscrição realizada com sucesso. Obrigado!']);
    exit;
}

$errorCode = $stmt->errno;
$stmt->close();
if ($errorCode === 1062) {
    echo json_encode(['success' => true, 'message' => 'Este e-mail já está inscrito na newsletter.']);
    exit;
}

error_log('Newsletter subscription insert failed with database error ' . $errorCode);
http_response_code(500);
echo json_encode(['success' => false, 'error' => 'Não foi possível concluir sua inscrição. Tente novamente mais tarde.']);
