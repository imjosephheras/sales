<?php
// ============================================================
// load_drafts.php - Cargar lista de formularios pendientes
// ============================================================

header('Content-Type: application/json');

// Incluir configuración de base de datos
require_once 'db_config.php';

try {
    $pdo = getDBConnection();
    
    // Cargar formularios con status 'draft' o 'pending'
    $sql = "SELECT 
                form_id,
                service_type,
                request_type,
                requested_service,
                client_name,
                total_cost,
                status,
                created_at,
                updated_at
            FROM forms
            WHERE status IN ('draft', 'pending')
            ORDER BY updated_at DESC, created_at DESC
            LIMIT 50";
    
    $stmt = $pdo->query($sql);
    $forms = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'forms' => $forms,
        'count' => count($forms)
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