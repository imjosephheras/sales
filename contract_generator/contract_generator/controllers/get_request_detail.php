<?php
/**
 * GET REQUEST DETAIL CONTROLLER
 * Devuelve todos los datos de una solicitud específica
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        throw new Exception('Request ID is required');
    }

    // Obtener datos completos
    $stmt = $pdo->prepare("
        SELECT * FROM requests 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request not found');
    }

    // Decodificar campos JSON
    if (!empty($request['Scope_Of_Work'])) {
        $request['Scope_Of_Work'] = json_decode($request['Scope_Of_Work'], true);
    }

    if (!empty($request['photos'])) {
        $request['photos'] = json_decode($request['photos'], true);
    }

    // Formatear fechas
    $request['created_at_formatted'] = date('M d, Y g:i A', strtotime($request['created_at']));
    
    if ($request['startDateServices']) {
        $request['startDateServices_formatted'] = date('Y-m-d', strtotime($request['startDateServices']));
    }

    echo json_encode([
        'success' => true,
        'data' => $request
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>