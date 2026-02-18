<?php
// ============================================================
// delete_draft.php - Eliminar borrador
// RBAC: Vendedor solo puede eliminar sus propias órdenes
// ============================================================

header('Content-Type: application/json');

// Incluir configuración de base de datos y RBAC
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/order_access.php';

try {
    // Enforce authentication + module access
    $currentUser = requireOrderAccess();

    $input = json_decode(file_get_contents('php://input'), true);
    $form_id = isset($input['form_id']) ? (int)$input['form_id'] : 0;

    if (!$form_id) {
        throw new Exception('Form ID is required');
    }

    $pdo = Database::getConnection();

    // RBAC: Verify the user has access to this order before deleting
    if (!canAccessOrder($pdo, $form_id, $currentUser)) {
        denyOrderAccess();
    }

    // Verificar que el formulario existe y es borrador
    $sql = "SELECT status FROM forms WHERE form_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();

    if (!$form) {
        throw new Exception('Form not found');
    }

    // Solo permitir eliminar borradores
    if ($form['status'] !== 'draft') {
        throw new Exception('Only draft forms can be deleted');
    }

    // Eliminar el formulario (CASCADE eliminará las relaciones automáticamente)
    $sql = "DELETE FROM forms WHERE form_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$form_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Draft deleted successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
