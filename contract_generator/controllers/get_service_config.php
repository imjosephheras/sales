<?php
/**
 * GET SERVICE REPORT CONFIGURATION
 * =================================
 * Returns the full service_report_config.php as JSON so the
 * front-end can dynamically render sections when the user
 * changes Service Type without a page reload.
 *
 * Usage:
 *   GET get_service_config.php           → full config
 *   GET get_service_config.php?key=roof_exterior → single type
 */

header('Content-Type: application/json; charset=UTF-8');

try {
    $allConfigs = require __DIR__ . '/../config/service_report_config.php';

    $key = $_GET['key'] ?? null;

    if ($key) {
        if (!isset($allConfigs[$key])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "Unknown service type: {$key}"]);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $allConfigs[$key]]);
    } else {
        echo json_encode(['success' => true, 'data' => $allConfigs]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
