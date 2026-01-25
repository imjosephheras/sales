<?php
/**
 * GET REQUESTS CONTROLLER
 * Devuelve la lista de solicitudes para el inbox con filtros
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    // Parámetros de filtro
    $filter_type = $_GET['type'] ?? '';
    $filter_status = $_GET['status'] ?? '';
    $filter_priority = $_GET['priority'] ?? '';
    $search = $_GET['search'] ?? '';

    // Construir query base
    $sql = "SELECT
                id,
                Request_Type,
                Priority,
                Company_Name,
                Requested_Service,
                status,
                created_at,
                docnum
            FROM requests
            WHERE 1=1";

    $params = [];

    // Aplicar filtros
    if (!empty($filter_type)) {
        $sql .= " AND Request_Type = :type";
        $params[':type'] = $filter_type;
    }

    if (!empty($filter_status)) {
        $sql .= " AND status = :status";
        $params[':status'] = $filter_status;
    }

    if (!empty($filter_priority)) {
        $sql .= " AND Priority = :priority";
        $params[':priority'] = $filter_priority;
    }

    if (!empty($search)) {
        $sql .= " AND (Company_Name LIKE :search OR Requested_Service LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // Ordenar por fecha (más recientes primero)
    $sql .= " ORDER BY created_at DESC";

    // Ejecutar query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear fechas
    foreach ($requests as &$request) {
        $request['created_at_formatted'] = date('M d, Y', strtotime($request['created_at']));
        $request['Company_Name'] = $request['Company_Name'] ?? 'No Company Name';
        $request['Requested_Service'] = $request['Requested_Service'] ?? 'No Service';
    }

    echo json_encode([
        'success' => true,
        'data' => $requests,
        'count' => count($requests)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>