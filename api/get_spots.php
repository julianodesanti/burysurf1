<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/db_config.php';

$spots = [];
$sql = "SELECT spot_id as id, spot_name as name, image FROM surf_spots ORDER BY spot_id";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $spots[] = $row;
    }
    $result->free();
}

echo json_encode($spots, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
$conn->close();
