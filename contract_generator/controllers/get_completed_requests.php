<?php
/**
 * GET COMPLETED REQUESTS CONTROLLER
 * Returns completed/ready forms (Contratos Generados).
 * Reads from forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/init.php';
$pdo = Database::getConnection();

try {
    // Search parameter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Build search condition
    $searchCondition = '';
    $searchParams = [];
    if ($search !== '') {
        $searchCondition = " AND (client_name LIKE :search OR company_name LIKE :search2)";
        $searchParams[':search'] = '%' . $search . '%';
        $searchParams[':search2'] = '%' . $search . '%';
    }

    // Get completed forms ordered by completion date (newest first)
    $sql = "SELECT
                form_id AS id,
                service_type AS Service_Type,
                request_type AS Request_Type,
                priority AS Priority,
                company_name AS Company_Name,
                client_name,
                email AS Email,
                requested_service AS Requested_Service,
                status,
                docnum,
                created_at,
                updated_at,
                completed_at
            FROM forms
            WHERE status IN ('ready', 'completed')" . $searchCondition . "
            ORDER BY completed_at DESC, updated_at DESC";

    $stmt = $pdo->prepare($sql);
    foreach ($searchParams as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for display
    foreach ($requests as &$request) {
        if ($request['completed_at']) {
            $request['completed_at_formatted'] = date('M d, Y g:i A', strtotime($request['completed_at']));
        }
        if ($request['created_at']) {
            $request['created_at_formatted'] = date('M d, Y g:i A', strtotime($request['created_at']));
        }

        $company = $request['Company_Name'] ?? 'No company';
        $request['client_name'] = $request['client_name'] ?? 'No client';
        $request['Priority'] = $request['Priority'] ?? 'Normal';
        $request['Service_Type'] = $request['Service_Type'] ?? 'N/A';
        $request['title'] = $company . ' - ' . $request['Service_Type'];
        $request['description'] = $request['Requested_Service'] ?? 'No description';

        // Map service type to category color and icon
        $service_lower = strtolower($request['Service_Type'] ?? '');
        if (strpos($service_lower, 'janitorial') !== false) {
            $request['category_color'] = '#28a745';
            $request['category_icon'] = '🧹';
        } elseif (strpos($service_lower, 'hospitality') !== false) {
            $request['category_color'] = '#17a2b8';
            $request['category_icon'] = '🏨';
        } elseif (strpos($service_lower, 'kitchen') !== false) {
            $request['category_color'] = '#fd7e14';
            $request['category_icon'] = '🍳';
        } elseif (strpos($service_lower, 'hood') !== false) {
            $request['category_color'] = '#6f42c1';
            $request['category_icon'] = '🔧';
        } else {
            $request['category_color'] = '#6c757d';
            $request['category_icon'] = '📋';
        }

        // Map request type to badge style
        $request_type = strtolower($request['Request_Type'] ?? 'jwo');
        if ($request_type === 'contract') {
            $request['type_color'] = '#28a745';
            $request['type_icon'] = '📝';
        } elseif ($request_type === 'jwo') {
            $request['type_color'] = '#fd7e14';
            $request['type_icon'] = '📋';
        } elseif ($request_type === 'proposal') {
            $request['type_color'] = '#6f42c1';
            $request['type_icon'] = '📄';
        } elseif ($request_type === 'quote') {
            $request['type_color'] = '#007bff';
            $request['type_icon'] = '💰';
        } else {
            $request['type_color'] = '#6c757d';
            $request['type_icon'] = '📋';
        }
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
