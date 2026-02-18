<?php
/**
 * DELETE REQUEST CONTROLLER
 * Deletes a form and all associated data.
 * Reads/writes from forms + contract_items (single source of truth).
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? (int)$input['id'] : 0;

    if (!$id) {
        throw new Exception('Form ID is required');
    }

    // Verify the form exists
    $stmt = $pdo->prepare("SELECT form_id, status, company_name FROM forms WHERE form_id = ?");
    $stmt->execute([$id]);
    $form = $stmt->fetch();

    if (!$form) {
        throw new Exception('Form not found');
    }

    // Delete related records
    $pdo->prepare("DELETE FROM scope_of_work WHERE form_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM scope_sections WHERE form_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM contract_items WHERE form_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM form_photos WHERE form_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM calendar_events WHERE form_id = ?")->execute([$id]);

    // Delete the form
    $pdo->prepare("DELETE FROM forms WHERE form_id = ?")->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Form deleted successfully'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
