<?php
/**
 * get_next_order_number.php
 * Returns the next available order number (1000-9999).
 * Reuses numbers from deleted/cancelled records.
 */

header('Content-Type: application/json');
require_once 'db_config.php';

try {
    $pdo = getDBConnection();

    // Get all currently used order numbers
    $stmt = $pdo->query("SELECT order_number FROM requests WHERE order_number IS NOT NULL ORDER BY order_number ASC");
    $used = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $usedSet = array_flip($used);

    // Find first available number in range 1000-9999
    $next = null;
    for ($i = 1000; $i <= 9999; $i++) {
        if (!isset($usedSet[$i])) {
            $next = $i;
            break;
        }
    }

    // If all are used, wrap around to 1000 (shouldn't happen with 9000 slots)
    if ($next === null) {
        $next = 1000;
    }

    echo json_encode(['next_number' => $next]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not determine next order number']);
}
