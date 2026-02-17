<?php
/**
 * save_task_tracking.php
 * Saves task tracking checkboxes state for a form.
 * Writes to forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input'
        ]);
        exit;
    }

    $request_id = $input['request_id'] ?? null;
    $task_tracking = $input['task_tracking'] ?? [];

    if (!$request_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing request ID'
        ]);
        exit;
    }

    $pdo = getDBConnection();

    // Encode task tracking as JSON
    $tracking_json = json_encode($task_tracking);

    // Update the form with task tracking (single source of truth)
    $stmt = $pdo->prepare("
        UPDATE forms
        SET task_tracking = :tracking,
            task_tracking_updated_at = NOW()
        WHERE form_id = :id
    ");

    $result = $stmt->execute([
        ':tracking' => $tracking_json,
        ':id' => $request_id
    ]);

    if ($result) {
        // Check if all tasks are complete to update ready_to_invoice
        $all_complete = isset($task_tracking['invoice_ready']) && $task_tracking['invoice_ready'] === true;

        if ($all_complete) {
            $stmt2 = $pdo->prepare("UPDATE forms SET ready_to_invoice = 1 WHERE form_id = :id");
            $stmt2->execute([':id' => $request_id]);
        } else {
            $stmt2 = $pdo->prepare("UPDATE forms SET ready_to_invoice = 0 WHERE form_id = :id");
            $stmt2->execute([':id' => $request_id]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Task tracking saved'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save tracking'
        ]);
    }

} catch (Exception $e) {
    error_log("Error saving task tracking: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
