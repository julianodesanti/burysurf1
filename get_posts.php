<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/api/db_config.php';

$tableSql = "CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    publication_date DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($tableSql);

$sql = "SELECT id, titulo, conteudo, imagem, publication_date FROM blog_posts ORDER BY publication_date DESC, id DESC";
$result = $conn->query($sql);

$posts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $result->free();
}

$conn->close();
echo json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
