<?php
/**
 * GET: /api/get_conditions.php
 * Returns all current conditions for all spots today
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db_config.php';

try {
    $sql = "SELECT ss.spot_id, ss.spot_name, sc.wave_size, sc.wave_formation, 
            sc.weather, sc.wind, sc.water_temperature, sc.updated_at
            FROM surf_spots ss
            LEFT JOIN surf_conditions sc ON ss.spot_id = sc.spot_id 
            AND sc.condition_date = CURDATE()
            ORDER BY ss.spot_name";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
        exit;
    }
    
    $conditions = [];
    while ($row = $result->fetch_assoc()) {
        $conditions[] = $row;
    }
    
    echo json_encode($conditions, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();