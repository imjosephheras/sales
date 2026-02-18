<?php
/**
 * GET REQUESTS CONTROLLER
 * Returns the list of forms for the inbox with filters.
 * Reads from forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    // Filter parameters
    $filter_type = $_GET['type'] ?? '';
    $filter_status = $_GET['status'] ?? '';
    $filter_priority = $_GET['priority'] ?? '';
    $search = $_GET['search'] ?? '';

    // Build base query - reading from forms instead of requests
    $sql = "SELECT
                form_id AS id,
                request_type AS Request_Type,
                priority AS Priority,
                company_name AS Company_Name,
                requested_service AS Requested_Service,
                status,
                created_at,
                docnum
            FROM forms
            WHERE 1=1";

    $params = [];

    // Apply filters
    if (!empty($filter_type)) {
        $sql .= " AND request_type = :type";
        $params[':type'] = $filter_type;
    }

    if (!empty($filter_status)) {
        $sql .= " AND status = :status";
        $params[':status'] = $filter_status;
    }

    if (!empty($filter_priority)) {
        $sql .= " AND priority = :priority";
        $params[':priority'] = $filter_priority;
    }

    if (!empty($search)) {
        $sql .= " AND (company_name LIKE :search OR requested_service LIKE :search2)";
        $params[':search'] = '%' . $search . '%';
        $params[':search2'] = '%' . $search . '%';
    }

    // Order by date (newest first)
    $sql .= " ORDER BY created_at DESC";

    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates
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
