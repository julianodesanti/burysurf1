<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!is_string($csrfToken) || empty($_SESSION['newsletter_csrf']) || !hash_equals($_SESSION['newsletter_csrf'], $csrfToken)) {
    http_response_code(403);
    exit('A sessão expirou. Atualize a página e tente novamente.');
}

$sql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(254) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    unsub_token CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_newsletter_subscribers_email (email),
    UNIQUE KEY uq_newsletter_subscribers_unsub_token (unsub_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($sql)) {
    error_log('Newsletter table migration failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível preparar a lista de inscritos. Consulte o log do servidor.');
}

$columnsResult = $conn->query("SHOW COLUMNS FROM newsletter_subscribers");
if (!$columnsResult) {
    error_log('Newsletter column inspection failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível verificar a estrutura da lista de inscritos.');
}

$columns = [];
while ($column = $columnsResult->fetch_assoc()) {
    $columns[$column['Field']] = true;
}
$columnsResult->free();

if (!isset($columns['created_at'])) {
    $alterResult = $conn->query("ALTER TABLE newsletter_subscribers ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
    if (!$alterResult) {
        error_log('Newsletter created_at column addition failed: ' . $conn->error);
        http_response_code(500);
        exit('Não foi possível preparar a data de inscrição.');
    }
}

if (!$conn->query("UPDATE newsletter_subscribers SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL OR CAST(created_at AS CHAR) LIKE '0000-00-00%'")) {
    error_log('Newsletter invalid date repair failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível corrigir as datas inválidas dos inscritos.');
}

if (!$conn->query("ALTER TABLE newsletter_subscribers MODIFY created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP")) {
    error_log('Newsletter created_at normalization failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível ajustar a coluna de data de inscrição.');
}

if (!isset($columns['unsub_token'])) {
    $alterResult = $conn->query("ALTER TABLE newsletter_subscribers ADD unsub_token CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL");
    if (!$alterResult) {
        error_log('Newsletter unsub_token column addition failed: ' . $conn->error);
        http_response_code(500);
        exit('Não foi possível preparar os tokens de cancelamento.');
    }
}

$tokenResult = $conn->query("SELECT id, unsub_token FROM newsletter_subscribers");
if (!$tokenResult) {
    error_log('Newsletter token lookup failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível verificar os tokens dos inscritos.');
}

$tokenUpdate = $conn->prepare("UPDATE newsletter_subscribers SET unsub_token = ? WHERE id = ?");
if (!$tokenUpdate) {
    error_log('Newsletter token update preparation failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível preparar os tokens de cancelamento.');
}

while ($row = $tokenResult->fetch_assoc()) {
    $currentToken = trim((string) ($row['unsub_token'] ?? ''));
    if (preg_match('/^[a-f0-9]{32,}$/i', $currentToken)) {
        $token = strtolower(substr($currentToken, 0, 32));
    } else {
        $token = bin2hex(random_bytes(16));
    }
    $subscriberId = (int) $row['id'];
    if ($token !== $currentToken) {
        $tokenUpdate->bind_param('si', $token, $subscriberId);
        if (!$tokenUpdate->execute()) {
            $error = $tokenUpdate->error;
            $tokenResult->free();
            $tokenUpdate->close();
            error_log('Newsletter token update failed: ' . $error);
            http_response_code(500);
            exit('Não foi possível gerar os tokens de cancelamento.');
        }
    }
}
$tokenResult->free();
$tokenUpdate->close();

$indexesResult = $conn->query("SHOW INDEX FROM newsletter_subscribers");
if (!$indexesResult) {
    error_log('Newsletter index inspection failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível verificar os índices da lista de inscritos.');
}

$indexes = [];
while ($index = $indexesResult->fetch_assoc()) {
    $indexes[$index['Key_name']] = true;
}
$indexesResult->free();

if (!$conn->query("ALTER TABLE newsletter_subscribers MODIFY unsub_token CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL")) {
    error_log('Newsletter token column normalization failed: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível ajustar a coluna de tokens de cancelamento.');
}

if (!isset($indexes['uq_newsletter_subscribers_unsub_token'])) {
    if (!$conn->query("ALTER TABLE newsletter_subscribers ADD UNIQUE KEY uq_newsletter_subscribers_unsub_token (unsub_token)")) {
        error_log('Newsletter token index creation failed: ' . $conn->error);
        http_response_code(500);
        exit('Não foi possível proteger a unicidade dos tokens de cancelamento.');
    }
}

header('Location: ../admin.php?tab=inscritos', true, 303);
exit;
