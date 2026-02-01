<?php
/**
 * get_pending_services.php
 * Returns all services with status = 'pending' (awaiting confirmation)
 * Also returns services with document status = 'completed' (contract generated)
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $pdo = getDBConnection();

    // Get filter from query string
    $filter = $_GET['filter'] ?? 'pending';
    $seller = $_GET['seller'] ?? '';
    $search = $_GET['search'] ?? '';

    // Build query based on filter
    $whereConditions = [];
    $params = [];

    if ($filter === 'pending') {
        // Services pending confirmation (document is completed but service not done yet)
        $whereConditions[] = "(service_status = 'pending' OR service_status IS NULL)";
        $whereConditions[] = "status = 'completed'"; // Document/contract is completed
    } elseif ($filter === 'all_pending') {
        // All pending (document pending + service pending)
        $whereConditions[] = "(service_status = 'pending' OR service_status IS NULL)";
    }

    // Filter by seller if provided
    if (!empty($seller)) {
        $whereConditions[] = "Seller = :seller";
        $params[':seller'] = $seller;
    }

    // Search filter
    if (!empty($search)) {
        $whereConditions[] = "(Company_Name LIKE :search OR client_name LIKE :search2 OR Order_Nomenclature LIKE :search3)";
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

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
            document_type,
            created_at,
            updated_at
        FROM requests
        $whereClause
        ORDER BY Work_Date ASC, created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();

    // Get count by service_status for summary
    $countSql = "
        SELECT
            COALESCE(service_status, 'pending') as status,
            COUNT(*) as count
        FROM requests
        WHERE status = 'completed'
        GROUP BY COALESCE(service_status, 'pending')
    ";
    $countStmt = $pdo->query($countSql);
    $counts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode([
        'success' => true,
        'services' => $services,
        'counts' => [
            'pending' => (int)($counts['pending'] ?? 0),
            'completed' => (int)($counts['completed'] ?? 0),
            'not_completed' => (int)($counts['not_completed'] ?? 0)
        ],
        'total' => count($services)
    ]);

} catch (Exception $e) {
    error_log("Error getting pending services: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading services: ' . $e->getMessage()
    ]);
}
?>
