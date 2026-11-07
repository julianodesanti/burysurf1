<?php
/**
 * GET: /api/get_historical_data.php
 * Returns historical data for a spot
 * Parameters: spot_id (required), days (optional, default: 30)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db_config.php';

try {
    $spotId = isset($_GET['spot_id']) ? intval($_GET['spot_id']) : 0;
    $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
    
    if (!$spotId) {
        http_response_code(400);
        echo json_encode(['error' => 'spot_id parameter is required']);
        exit;
    }
    
    $sql = "SELECT condition_date, wave_size, wave_formation, weather, wind, water_temperature
            FROM surf_conditions
            WHERE spot_id = ?
            ORDER BY condition_date DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $spotId, $days);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();