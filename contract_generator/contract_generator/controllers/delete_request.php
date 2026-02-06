<?php
/**
 * DELETE REQUEST CONTROLLER
 * Deletes a request (pending task or generated contract) from the database
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? (int)$input['id'] : 0;

    if (!$id) {
        throw new Exception('Request ID is required');
    }

    // Verify the request exists
    $stmt = $pdo->prepare("SELECT id, status, Company_Name FROM requests WHERE id = ?");
    $stmt->execute([$id]);
    $request = $stmt->fetch();

    if (!$request) {
        throw new Exception('Request not found');
    }

    // Delete related form data if linked
    $stmt = $pdo->prepare("SELECT form_id FROM requests WHERE id = ? AND form_id IS NOT NULL");
    $stmt->execute([$id]);
    $formLink = $stmt->fetch();

    if ($formLink && $formLink['form_id']) {
        // Delete related form records (scope_of_work, costs, etc.)
        $formId = (int)$formLink['form_id'];
        $pdo->prepare("DELETE FROM scope_of_work WHERE form_id = ?")->execute([$formId]);
        $pdo->prepare("DELETE FROM janitorial_services_costs WHERE form_id = ?")->execute([$formId]);
        $pdo->prepare("DELETE FROM kitchen_cleaning_costs WHERE form_id = ?")->execute([$formId]);
        $pdo->prepare("DELETE FROM hood_vent_costs WHERE form_id = ?")->execute([$formId]);
    }

    // Delete the request
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Request deleted successfully'
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
