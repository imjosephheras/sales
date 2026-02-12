<?php
/**
 * GET SERVICE TYPES API
 * Returns all distinct service_type values from the three cost tables:
 *   - hood_vent_costs
 *   - janitorial_services_costs
 *   - kitchen_cleaning_costs
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/order_access.php';
require_once __DIR__ . '/../form_contract/db_config.php';

Middleware::module('calendar');

try {
    $pdo = getDBConnection();

    $sql = "
        SELECT DISTINCT service_type FROM (
            SELECT service_type FROM hood_vent_costs
            WHERE service_type IS NOT NULL AND service_type != ''
            UNION
            SELECT service_type FROM janitorial_services_costs
            WHERE service_type IS NOT NULL AND service_type != ''
            UNION
            SELECT service_type FROM kitchen_cleaning_costs
            WHERE service_type IS NOT NULL AND service_type != ''
        ) AS all_services
        ORDER BY service_type ASC
    ";

    $stmt = $pdo->query($sql);
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'service_types' => $types
    ]);

} catch (Exception $e) {
    error_log("Calendar get_service_types error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'service_types' => []
    ]);
}
?>
