<?php
// ============================================================
// load_drafts_by_seller.php - Cargar formularios por vendedor
// Soporta paginación con parámetros page y limit
// ============================================================

header('Content-Type: application/json');

// Incluir configuración de base de datos
require_once 'db_config.php';

try {
    $pdo = getDBConnection();

    // Pagination parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM forms WHERE status IN ('draft', 'pending')";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute();
    $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = max(1, ceil($totalCount / $limit));

    // Cargar formularios con paginación
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
            ORDER BY updated_at DESC, created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $forms = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'forms' => $forms,
        'count' => count($forms),
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_count' => $totalCount,
            'total_pages' => $totalPages
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'forms' => [],
        'pagination' => ['page' => 1, 'limit' => 20, 'total_count' => 0, 'total_pages' => 1]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'forms' => [],
        'pagination' => ['page' => 1, 'limit' => 20, 'total_count' => 0, 'total_pages' => 1]
    ]);
}
?>
