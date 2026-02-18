<?php
/**
 * GET SERVICE TYPES API
 * Returns all distinct service_type values from the contract_items table.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../form_contract/order_access.php';
require_once __DIR__ . '/../form_contract/db_config.php';

Middleware::module('calendar');

try {
    $pdo = getDBConnection();

    $sql = "
        SELECT DISTINCT service_type
        FROM contract_items
        WHERE service_type IS NOT NULL AND service_type != ''
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
