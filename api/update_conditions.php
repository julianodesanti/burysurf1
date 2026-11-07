<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_config.php';

try {

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
        exit;
    }

    // Validate required fields
    $required = [
        'spot_id',
        'condition_date',
        'wave_size',
        'wave_formation',
        'weather',
        'wind',
        'water_temperature'
    ];

    foreach ($required as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
            exit;
        }
    }

    $spotId = intval($data['spot_id']);
    $date = $data['condition_date'];
    $waveSize = $data['wave_size'];
    $waveFormation = $data['wave_formation'];
    $weather = $data['weather'];
    $wind = $data['wind'];
    $waterTemp = $data['water_temperature'];

    // Check if record exists
    $checkSql = "SELECT id FROM surf_conditions WHERE spot_id = ? AND condition_date = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("is", $spotId, $date);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->num_rows > 0;
    $checkStmt->close();

    if ($exists) {
        $sql = "UPDATE surf_conditions 
                SET wave_size = ?, wave_formation = ?, weather = ?, 
                    wind = ?, water_temperature = ?
                WHERE spot_id = ? AND condition_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssis", $waveSize, $waveFormation, $weather, $wind, $waterTemp, $spotId, $date);
    } else {
        $sql = "INSERT INTO surf_conditions 
                (spot_id, condition_date, wave_size, wave_formation, weather, wind, water_temperature)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $spotId, $date, $waveSize, $waveFormation, $weather, $wind, $waterTemp);
    }

    if ($stmt->execute()) {
        ob_end_clean();
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
exit;