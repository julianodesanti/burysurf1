<?php
// --- Output buffering to prevent PHP warnings from corrupting JSON ---
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Method not allowed']);
    exit;
}

$spot_id = isset($_POST['spot_id']) ? intval($_POST['spot_id']) : 0;
if (!$spot_id || empty($_FILES['image'])) {
    http_response_code(400);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Missing spot_id or image']);
    exit;
}

if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'File upload error: ' . $_FILES['image']['error']]);
    exit;
}

// Validate file type
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
    echo json_encode(['status' => 'error', 'error' => 'Invalid image type: ' . $mime]);
    exit;
}

// Save file
$uploadDir = dirname(__DIR__) . '/upload_spots/';
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
$filename = 'spot_' . $spot_id . '_' . time() . '.' . strtolower($ext);
$target = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['status' => 'error', 'error' => 'Failed to save file']);
    exit;
}

// Update DB
$stmt = $conn->prepare("UPDATE surf_spots SET image = ? WHERE spot_id = ?");
$stmt->bind_param('si', $filename, $spot_id);
$stmt->execute();
$stmt->close();

ob_end_clean();
echo json_encode(['status' => 'ok', 'filename' => $filename], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
$conn->close();
exit;
