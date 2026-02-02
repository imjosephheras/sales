<?php
// ============================================================
// load_drafts_by_seller.php - Cargar formularios por vendedor
// Solo muestra los formularios del vendedor especificado
// ============================================================

header('Content-Type: application/json');

// Incluir configuración de base de datos
require_once 'db_config.php';

try {
    $pdo = getDBConnection();

    // Obtener el vendedor del parámetro GET
    $seller = isset($_GET['seller']) ? trim($_GET['seller']) : '';

    if (empty($seller)) {
        // Si no hay vendedor, devolver lista vacía
        echo json_encode([
            'success' => true,
            'forms' => [],
            'count' => 0,
            'message' => 'No seller specified'
        ]);
        exit;
    }

    // Cargar formularios del vendedor específico con status 'draft' o 'pending'
    $sql = "SELECT
                form_id,
                service_type,
                request_type,
                requested_service,
                client_name,
                total_cost,
                seller,
                status,
                service_status,
                created_at,
                updated_at
            FROM forms
            WHERE status IN ('draft', 'pending')
            AND seller = :seller
            ORDER BY updated_at DESC, created_at DESC
            LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':seller', $seller);
    $stmt->execute();
    $forms = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'forms' => $forms,
        'count' => count($forms),
        'seller' => $seller
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'forms' => []
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'forms' => []
    ]);
}
?>
