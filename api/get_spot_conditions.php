<?php
/**
 * GET: /api/get_spot_conditions.php
 * Returns conditions for a specific spot
 * Parameters: spot_id (required)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db_config.php';

try {
    $spotId = isset($_GET['spot_id']) ? intval($_GET['spot_id']) : 0;
    
    if (!$spotId) {
        http_response_code(400);
        echo json_encode(['error' => 'spot_id parameter is required']);
        exit;
    }
    
    $sql = "SELECT ss.spot_id, ss.spot_name, sc.wave_size, sc.wave_formation, 
            sc.weather, sc.wind, sc.water_temperature, sc.updated_at
            FROM surf_spots ss
            LEFT JOIN surf_conditions sc ON ss.spot_id = sc.spot_id
            WHERE ss.spot_id = ? AND sc.condition_date = CURDATE()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $spotId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $condition = $result->fetch_assoc();
    
    if ($condition) {
        echo json_encode($condition, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Conditions not found'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
