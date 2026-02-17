<?php
/**
 * get_pending_services.php
 * Returns all services for tracking in Admin Panel.
 * Reads from forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $pdo = getDBConnection();

    // Get filter from query string
    $seller = $_GET['seller'] ?? '';
    $search = $_GET['search'] ?? '';
    $progress = $_GET['progress'] ?? ''; // not_started, in_progress, completed

    // Build query based on filter
    $whereConditions = [];
    $params = [];

    // Only show completed documents (contract generated)
    $whereConditions[] = "status = 'completed'";

    // Filter by seller if provided
    if (!empty($seller)) {
        $whereConditions[] = "seller = :seller";
        $params[':seller'] = $seller;
    }

    // Search filter
    if (!empty($search)) {
        $whereConditions[] = "(company_name LIKE :search OR client_name LIKE :search2 OR Order_Nomenclature LIKE :search3)";
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    $sql = "
        SELECT
            form_id AS id,
            service_type AS Service_Type,
            request_type AS Request_Type,
            company_name AS Company_Name,
            client_name,
            email AS Email,
            phone AS Number_Phone,
            seller AS Seller,
            total_cost AS PriceInput,
            Work_Date,
            Document_Date,
            Order_Nomenclature,
            order_number,
            status,
            service_status,
            task_tracking,
            admin_notes,
            request_type AS document_type,
            created_at,
            updated_at
        FROM forms
        $whereClause
        ORDER BY Work_Date ASC, created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();

    // Define total number of tasks
    $total_tasks = 9;

    // Filter by progress if provided (done in PHP to handle JSON parsing)
    if (!empty($progress)) {
        $services = array_filter($services, function($service) use ($progress, $total_tasks) {
            $tracking = json_decode($service['task_tracking'] ?? '{}', true) ?: [];
            $completed = count(array_filter($tracking, function($v) { return $v === true; }));

            switch ($progress) {
                case 'not_started':
                    return $completed === 0;
                case 'in_progress':
                    return $completed > 0 && $completed < $total_tasks;
                case 'completed':
                    return $completed === $total_tasks;
                default:
                    return true;
            }
        });
        $services = array_values($services); // Re-index array
    }

    // Calculate progress stats
    $stats = ['not_started' => 0, 'in_progress' => 0, 'completed' => 0];
    foreach ($services as $service) {
        $tracking = json_decode($service['task_tracking'] ?? '{}', true) ?: [];
        $completed = count(array_filter($tracking, function($v) { return $v === true; }));

        if ($completed === 0) {
            $stats['not_started']++;
        } elseif ($completed === $total_tasks) {
            $stats['completed']++;
        } else {
            $stats['in_progress']++;
        }
    }

    echo json_encode([
        'success' => true,
        'services' => $services,
        'stats' => $stats,
        'total' => count($services)
    ]);

} catch (Exception $e) {
    error_log("Error getting services: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading services: ' . $e->getMessage()
    ]);
}
?>
