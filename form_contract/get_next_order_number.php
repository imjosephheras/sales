<?php
/**
 * get_next_order_number.php
 * Returns the next available order number (100000-999999).
 * Reuses numbers from deleted/cancelled records.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/init.php';

try {
    $pdo = Database::getConnection();

    // Get all currently used order numbers
    $stmt = $pdo->query("SELECT order_number FROM forms WHERE order_number IS NOT NULL ORDER BY order_number ASC");
    $used = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $usedSet = array_flip($used);

    // Find first available number in range 100000-999999
    $next = null;
    for ($i = 100000; $i <= 999999; $i++) {
        if (!isset($usedSet[$i])) {
            $next = $i;
            break;
        }
    }

    // If all are used, wrap around to 100000 (shouldn't happen with 900000 slots)
    if ($next === null) {
        $next = 100000;
    }

    echo json_encode(['next_number' => $next]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not determine next order number']);
}
