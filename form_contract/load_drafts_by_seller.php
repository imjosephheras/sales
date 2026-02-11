<?php
// ============================================================
// load_drafts_by_seller.php - Cargar formularios por vendedor
// Soporta paginación con parámetros page y limit
// RBAC: Vendedor solo ve sus propias órdenes; Leader/Admin ven todas
// ============================================================

header('Content-Type: application/json');

// Incluir configuración de base de datos y RBAC
require_once 'db_config.php';
require_once 'order_access.php';

try {
    // Enforce authentication + module access; get current user
    $currentUser = requireOrderAccess();
    $rbac = getOrderRbacFilter($currentUser);

    $pdo = getDBConnection();

    // ── TEMPORARY DEBUG: Log seller matching details ──
    // TODO: Remove this debug block once the Kenny Howe issue is confirmed resolved
    $debugSellerName = trim($currentUser['full_name']);
    $debugStmt = $pdo->prepare("SELECT DISTINCT seller, HEX(seller) as seller_hex, LENGTH(seller) as seller_len FROM forms WHERE status IN ('draft','pending') AND seller IS NOT NULL");
    $debugStmt->execute();
    $debugSellers = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("[SELLER-DEBUG] Authenticated user: '{$debugSellerName}' (length=" . strlen($debugSellerName) . ", hex=" . bin2hex($debugSellerName) . ", role_id={$currentUser['role_id']})");
    foreach ($debugSellers as $ds) {
        $match = (mb_strtolower(trim($ds['seller'])) === mb_strtolower($debugSellerName)) ? 'MATCH' : 'NO-MATCH';
        error_log("[SELLER-DEBUG] DB seller: '{$ds['seller']}' (length={$ds['seller_len']}, hex={$ds['seller_hex']}) => {$match}");
    }
    // ── END TEMPORARY DEBUG ──

    // Pagination parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;

    // Get total count for pagination (with RBAC filter)
    $countSql = "SELECT COUNT(*) as total FROM forms WHERE status IN ('draft', 'pending') " . $rbac['sql'];
    $countStmt = $pdo->prepare($countSql);
    foreach ($rbac['params'] as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = max(1, ceil($totalCount / $limit));

    // Cargar formularios con paginación (with RBAC filter)
    $sql = "SELECT
                form_id,
                service_type,
                request_type,
                requested_service,
                client_name,
                company_name,
                order_number,
                total_cost,
                seller,
                status,
                service_status,
                created_at,
                updated_at
            FROM forms
            WHERE status IN ('draft', 'pending') " . $rbac['sql'] . "
            ORDER BY updated_at DESC, created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($rbac['params'] as $key => $value) {
        $stmt->bindValue($key, $value);
    }
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
