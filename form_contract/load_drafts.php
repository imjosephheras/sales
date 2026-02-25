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
    error_log("load_drafts DB error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'A database error occurred. Please try again later.',
        'forms' => []
    ]);
} catch (Exception $e) {
    error_log("load_drafts error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.',
        'forms' => []
    ]);
}
?>