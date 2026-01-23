<?php
/**
 * GET PENDING REQUESTS CONTROLLER
 * Returns pending requests from form_contract submissions
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    // Get pending requests ordered by priority and date
    $sql = "SELECT
                id,
                Service_Type,
                Request_Type,
                Priority,
                Company_Name,
                client_name,
                Email,
                Requested_Service,
                status,
                created_at,
                updated_at
            FROM requests
            WHERE status IN ('pending', 'in_progress')
            ORDER BY
                FIELD(Priority, 'Urgent', 'High', 'Normal', 'Low'),
                created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for display
    foreach ($requests as &$request) {
        // Format dates
        if ($request['created_at']) {
            $request['created_at_formatted'] = date('M d, Y g:i A', strtotime($request['created_at']));
        }

        // Ensure fields are not null
        $request['Company_Name'] = $request['Company_Name'] ?? 'No company';
        $request['client_name'] = $request['client_name'] ?? 'No client';
        $request['Priority'] = $request['Priority'] ?? 'Normal';
        $request['Service_Type'] = $request['Service_Type'] ?? 'N/A';

        // Add display title
        $request['title'] = $request['Company_Name'] . ' - ' . $request['Service_Type'];
        $request['description'] = $request['Requested_Service'] ?? 'No description';

        // Map priority to badge color
        $priority_lower = strtolower($request['Priority']);
        if ($priority_lower === 'urgent') {
            $request['priority_color'] = '#dc3545';
        } elseif ($priority_lower === 'high') {
            $request['priority_color'] = '#fd7e14';
        } elseif ($priority_lower === 'low') {
            $request['priority_color'] = '#6c757d';
        } else {
            $request['priority_color'] = '#007bff';
        }

        // Map service type to category color
        $service_lower = strtolower($request['Service_Type']);
        if (strpos($service_lower, 'janitorial') !== false) {
            $request['category_color'] = '#28a745';
            $request['category_icon'] = '🧹';
        } elseif (strpos($service_lower, 'hospitality') !== false) {
            $request['category_color'] = '#17a2b8';
            $request['category_icon'] = '🏨';
        } else {
            $request['category_color'] = '#6c757d';
            $request['category_icon'] = '📋';
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
