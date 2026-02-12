<?php
/**
 * GET PENDING REQUESTS CONTROLLER
 * Returns pending requests from form_contract submissions
 * Includes drafts (pre-saved forms) for preview and completion
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    // Pagination parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;

    // Search parameter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Build search condition
    $searchCondition = '';
    $searchParams = [];
    if ($search !== '') {
        $searchCondition = " AND (r.client_name LIKE :search OR r.Company_Name LIKE :search2)";
        $searchParams[':search'] = '%' . $search . '%';
        $searchParams[':search2'] = '%' . $search . '%';
    }

    // Get total count first
    $countSql = "SELECT COUNT(*) as total
                 FROM requests r
                 WHERE r.status IN ('pending', 'in_progress', 'draft')" . $searchCondition;
    $countStmt = $pdo->prepare($countSql);
    foreach ($searchParams as $key => $val) {
        $countStmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = max(1, ceil($totalCount / $limit));

    // Get pending requests including drafts, ordered by priority and date
    $sql = "SELECT
                r.id,
                r.Service_Type,
                r.Request_Type,
                r.Priority,
                r.Company_Name,
                r.client_name,
                r.Client_Title,
                r.Email,
                r.Number_Phone,
                r.Company_Address,
                r.Requested_Service,
                r.status,
                r.docnum,
                r.created_at,
                r.updated_at,
                r.Seller,
                r.PriceInput,
                r.Invoice_Frequency,
                r.Contract_Duration,
                r.includeJanitorial,
                r.includeKitchen,
                r.includeStaff,
                r.grand18,
                r.grand19,
                r.totalArea,
                r.startDateServices,
                r.Site_Observation,
                r.Scope_Of_Work,
                f.form_id,
                f.Work_Date,
                f.Document_Date,
                f.Order_Nomenclature,
                f.order_number,
                f.total_cost
            FROM requests r
            LEFT JOIN forms f ON r.form_id = f.form_id OR (r.form_id IS NULL AND r.docnum = f.Order_Nomenclature)
            WHERE r.status IN ('pending', 'in_progress', 'draft')" . $searchCondition . "
            ORDER BY
                FIELD(r.status, 'draft', 'pending', 'in_progress'),
                FIELD(r.Priority, 'Urgent', 'High', 'Normal', 'Low'),
                r.created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($searchParams as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for display
    foreach ($requests as &$request) {
        // Format dates
        if ($request['created_at']) {
            $request['created_at_formatted'] = date('M d, Y g:i A', strtotime($request['created_at']));
        }
        if ($request['Work_Date']) {
            $request['work_date_formatted'] = date('M d, Y', strtotime($request['Work_Date']));
        }
        if ($request['Document_Date']) {
            $request['document_date_formatted'] = date('M d, Y', strtotime($request['Document_Date']));
        }
        if ($request['startDateServices']) {
            $request['start_date_formatted'] = date('M d, Y', strtotime($request['startDateServices']));
        }

        // Ensure fields are not null
        $request['Company_Name'] = $request['Company_Name'] ?? 'No company';
        $request['client_name'] = $request['client_name'] ?? 'No client';
        $request['Priority'] = $request['Priority'] ?? 'Normal';
        $request['Service_Type'] = $request['Service_Type'] ?? 'N/A';

        // Add display title with order nomenclature if available
        if (!empty($request['Order_Nomenclature'])) {
            $request['title'] = $request['Order_Nomenclature'] . ' - ' . $request['Company_Name'];
        } else {
            $request['title'] = $request['Company_Name'] . ' - ' . $request['Service_Type'];
        }
        $request['description'] = $request['Requested_Service'] ?? 'No description';

        // Determine if this is a draft (incomplete form)
        $request['is_draft'] = ($request['status'] === 'draft');

        // Calculate completion percentage for drafts
        $request['completion_info'] = calculateCompletionInfo($request);

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

        // Map service type to category color and icon
        $service_lower = strtolower($request['Service_Type'] ?? '');
        if (strpos($service_lower, 'janitorial') !== false) {
            $request['category_color'] = '#28a745';
            $request['category_icon'] = '🧹';
        } elseif (strpos($service_lower, 'hospitality') !== false) {
            $request['category_color'] = '#17a2b8';
            $request['category_icon'] = '🏨';
        } elseif (strpos($service_lower, 'hood') !== false || strpos($service_lower, 'vent') !== false) {
            $request['category_color'] = '#fd7e14';
            $request['category_icon'] = '🔥';
        } elseif (strpos($service_lower, 'kitchen') !== false) {
            $request['category_color'] = '#20c997';
            $request['category_icon'] = '🍳';
        } else {
            $request['category_color'] = '#6c757d';
            $request['category_icon'] = '📋';
        }

        // Status badge info
        if ($request['is_draft']) {
            $request['status_color'] = '#ffc107';
            $request['status_icon'] = '📝';
            $request['status_label'] = 'Draft';
        } elseif ($request['status'] === 'in_progress') {
            $request['status_color'] = '#17a2b8';
            $request['status_icon'] = '⏳';
            $request['status_label'] = 'In Progress';
        } else {
            $request['status_color'] = '#007bff';
            $request['status_icon'] = '📥';
            $request['status_label'] = 'Pending';
        }

        // Determine available report types
        $request['available_reports'] = [];
        if ($request['includeKitchen'] === 'Yes') {
            $request['available_reports'][] = 'hood_vent';
            $request['available_reports'][] = 'kitchen';
        }
        if ($request['includeJanitorial'] === 'Yes') {
            $request['available_reports'][] = 'janitorial';
        }
        if ($request['includeStaff'] === 'Yes') {
            $request['available_reports'][] = 'staff';
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $requests,
        'count' => count($requests),
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_count' => $totalCount,
            'total_pages' => $totalPages
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

/**
 * Calculate completion info for a request/draft
 */
function calculateCompletionInfo($request) {
    $completed = 0;
    $total = 0;
    $missing = [];

    // Section 1: Request Info
    $total += 2;
    if (!empty($request['Service_Type'])) $completed++; else $missing[] = 'Service Type';
    if (!empty($request['Request_Type'])) $completed++; else $missing[] = 'Request Type';

    // Section 2: Client Info
    $total += 3;
    if (!empty($request['Company_Name']) && $request['Company_Name'] !== 'No company') $completed++; else $missing[] = 'Company Name';
    if (!empty($request['client_name']) && $request['client_name'] !== 'No client') $completed++; else $missing[] = 'Client Name';
    if (!empty($request['Number_Phone'])) $completed++; else $missing[] = 'Phone';

    // Section 3: Operational
    $total += 2;
    if (!empty($request['Invoice_Frequency'])) $completed++; else $missing[] = 'Invoice Frequency';
    if (!empty($request['Contract_Duration'])) $completed++; else $missing[] = 'Contract Duration';

    // Section 4: Economic
    $total += 2;
    if (!empty($request['Seller'])) $completed++; else $missing[] = 'Seller';
    if (!empty($request['PriceInput']) || !empty($request['total_cost']) || !empty($request['grand18']) || !empty($request['grand19'])) {
        $completed++;
    } else {
        $missing[] = 'Pricing';
    }

    // Section 5: Dates
    $total += 1;
    if (!empty($request['Work_Date'])) $completed++; else $missing[] = 'Work Date';

    $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

    return [
        'completed' => $completed,
        'total' => $total,
        'percentage' => $percentage,
        'missing' => array_slice($missing, 0, 3) // Show first 3 missing items
    ];
}
?>
