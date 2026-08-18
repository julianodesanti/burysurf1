<?php
file_put_contents(__DIR__ . '/test_php.log', "PHP is working at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
echo json_encode(['status' => 'ok']);
