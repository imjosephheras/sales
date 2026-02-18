<?php
/**
 * save_admin_notes.php
 * Saves internal admin notes for a form.
 * Writes to forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../form_contract/init.php';

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
    $admin_notes = $input['admin_notes'] ?? '';

    if (!$request_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing request ID'
        ]);
        exit;
    }

    $pdo = Database::getConnection();

    // Update the form with admin notes (single source of truth)
    $stmt = $pdo->prepare("
        UPDATE forms
        SET admin_notes = :notes
        WHERE form_id = :id
    ");

    $result = $stmt->execute([
        ':notes' => $admin_notes,
        ':id' => $request_id
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Notes saved'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save notes'
        ]);
    }

} catch (Exception $e) {
    error_log("Error saving admin notes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
