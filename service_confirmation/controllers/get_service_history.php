<?php
/**
 * get_service_history.php
 * Returns complete history of all Request Forms (never deleted)
 * Supports filtering and pagination
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $pdo = getDBConnection();

    // Get filter parameters
    $status = $_GET['status'] ?? 'all'; // all, completed, not_completed, pending
    $seller = $_GET['seller'] ?? '';
    $search = $_GET['search'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));
    $offset = ($page - 1) * $limit;

    $whereConditions = [];
    $params = [];

    // Filter by service status (single source of truth)
    if ($status !== 'all') {
        if ($status === 'pending') {
            $whereConditions[] = "(service_status = 'pending' OR service_status IS NULL)";
        } elseif ($status === 'in_progress') {
            $whereConditions[] = "service_status IN ('scheduled', 'confirmed', 'in_progress')";
        } else {
            $whereConditions[] = "service_status = :status";
            $params[':status'] = $status;
        }
    }

    // Filter by seller
    if (!empty($seller)) {
        $whereConditions[] = "Seller = :seller";
        $params[':seller'] = $seller;
    }

    // Search filter
    if (!empty($search)) {
        $whereConditions[] = "(Company_Name LIKE :search OR client_name LIKE :search2 OR Order_Nomenclature LIKE :search3 OR Email LIKE :search4)";
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
        $params[':search4'] = "%$search%";
    }

    // Date range filter
    if (!empty($date_from)) {
        $whereConditions[] = "DATE(created_at) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    if (!empty($date_to)) {
        $whereConditions[] = "DATE(created_at) <= :date_to";
        $params[':date_to'] = $date_to;
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM requests $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();

    // Get records
    $sql = "
        SELECT
            id,
            Service_Type,
            Request_Type,
            Company_Name,
            client_name,
            Email,
            Number_Phone,
            Seller,
            PriceInput,
            Work_Date,
            Document_Date,
            Order_Nomenclature,
            order_number,
            status,
            service_status,
            task_tracking,
            admin_notes,
            service_completed_at,
            ready_to_invoice,
            final_pdf_path,
            document_type,
            created_at,
            updated_at,
            completed_at
        FROM requests
        $whereClause
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);

    // Bind all params
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $records = $stmt->fetchAll();

    // Get statistics based on service_status (single source of truth)
    $statsSql = "
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN service_status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN service_status IN ('scheduled', 'confirmed', 'in_progress') THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN service_status = 'pending' OR service_status IS NULL THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN ready_to_invoice = 1 THEN 1 ELSE 0 END) as ready_to_invoice
        FROM requests
    ";
    $statsStmt = $pdo->query($statsSql);
    $stats = $statsStmt->fetch();

    // Get unique sellers for filter
    $sellersSql = "SELECT DISTINCT Seller FROM requests WHERE Seller IS NOT NULL AND Seller != '' ORDER BY Seller";
    $sellersStmt = $pdo->query($sellersSql);
    $sellers = $sellersStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'records' => $records,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_records' => (int)$totalRecords,
            'total_pages' => ceil($totalRecords / $limit)
        ],
        'stats' => [
            'total' => (int)$stats['total'],
            'completed' => (int)$stats['completed'],
            'in_progress' => (int)$stats['in_progress'],
            'pending' => (int)$stats['pending'],
            'ready_to_invoice' => (int)$stats['ready_to_invoice']
        ],
        'sellers' => $sellers
    ]);

} catch (Exception $e) {
    error_log("Error getting service history: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading history: ' . $e->getMessage()
    ]);
}
?>
