<?php
/**
 * get_service_detail.php
 * Returns detailed information about a specific request/service
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $request_id = $_GET['id'] ?? null;

    if (!$request_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing request ID'
        ]);
        exit;
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = :id");
    $stmt->execute([':id' => $request_id]);
    $request = $stmt->fetch();

    if (!$request) {
        echo json_encode([
            'success' => false,
            'message' => 'Request not found'
        ]);
        exit;
    }

    // Parse JSON fields
    $json_fields = ['week_days', 'Scope_Of_Work', 'photos', 'type18', 'write18', 'time18', 'freq18', 'desc18', 'subtotal18', 'type19', 'time19', 'freq19', 'desc19', 'subtotal19', 'base_staff', 'increase_staff', 'bill_staff'];

    foreach ($json_fields as $field) {
        if (isset($request[$field]) && !empty($request[$field])) {
            $decoded = json_decode($request[$field], true);
            if ($decoded !== null) {
                $request[$field . '_parsed'] = $decoded;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'request' => $request
    ]);

} catch (Exception $e) {
    error_log("Error getting service detail: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading service: ' . $e->getMessage()
    ]);
}
?>
