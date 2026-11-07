<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Method not allowed']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if ($title === '' || $content === '' || empty($_FILES['image'])) {
    http_response_code(400);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Título, texto e imagem são obrigatórios']);
    exit;
}

if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Erro no upload da imagem: ' . $_FILES['image']['error']]);
    exit;
}

$mime = '';
if (function_exists('finfo_open') && function_exists('finfo_file')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo !== false) {
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
    }
}

if ($mime === '' && function_exists('mime_content_type')) {
    $mime = mime_content_type($_FILES['image']['tmp_name']);
}

if (!is_string($mime) || !preg_match('/^image\/(jpeg|png|gif|webp)$/', $mime)) {
    http_response_code(400);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Tipo de imagem inválido: ' . $mime]);
    exit;
}

$uploadDir = dirname(__DIR__) . '/upload_posts/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        http_response_code(500);
        ob_end_clean();
        echo json_encode(['status' => 'error', 'error' => 'Falha ao criar diretório de upload']);
        exit;
    }
}

if (!is_writable($uploadDir)) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Diretório de upload não é gravável']);
    exit;
}

$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = 'blog_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . strtolower($ext);
$target = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Falha ao salvar a imagem']);
    exit;
}

$tableSql = "CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    publication_date DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($tableSql);

$stmt = $conn->prepare("INSERT INTO blog_posts (titulo, conteudo, imagem, publication_date) VALUES (?, ?, ?, ?)");
$now = date('Y-m-d H:i:s');
$stmt->bind_param('ssss', $title, $content, $filename, $now);

if ($stmt->execute()) {
    ob_end_clean();
    echo json_encode(['status' => 'ok', 'post_id' => $stmt->insert_id], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} else {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Erro no banco de dados']);
}

$stmt->close();
$conn->close();
exit;
