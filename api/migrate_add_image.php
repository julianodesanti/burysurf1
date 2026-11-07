<?php
/**
 * Migration: add `image` column to surf_spots if it doesn't exist
 * Run this once via browser or CLI: php migrate_add_image.php
 */
require_once __DIR__ . '/db_config.php';

$schema = DB_NAME ?? 'burysurf';
$checkSql = "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'surf_spots' AND COLUMN_NAME = 'image'";

$stmt = $conn->prepare($checkSql);
$stmt->bind_param('s', $schema);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if ($res && intval($res['cnt']) > 0) {
    echo json_encode(['status' => 'skipped', 'message' => 'Column `image` already exists.']);
    exit;
}

$alter = "ALTER TABLE surf_spots ADD COLUMN image VARCHAR(255) DEFAULT NULL";
if ($conn->query($alter) === TRUE) {
    echo json_encode(['status' => 'ok', 'message' => 'Column `image` added to surf_spots.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $conn->error]);
}

$conn->close();