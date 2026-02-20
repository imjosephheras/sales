<?php
/**
 * GET PENDING REQUESTS CONTROLLER
 * Returns pending forms from form_contract submissions.
 * Reads from forms table (single source of truth).
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
        $searchCondition = " AND (f.client_name LIKE :search OR f.company_name LIKE :search2 OR f.seller LIKE :search3 OR f.Order_Nomenclature LIKE :search4)";
        $searchParams[':search'] = '%' . $search . '%';
        $searchParams[':search2'] = '%' . $search . '%';
        $searchParams[':search3'] = '%' . $search . '%';
        $searchParams[':search4'] = '%' . $search . '%';
    }

    // Get total count first
    $countSql = "SELECT COUNT(*) as total
                 FROM forms f
                 WHERE f.status IN ('pending', 'in_progress', 'draft')" . $searchCondition;
    $countStmt = $pdo->prepare($countSql);
    foreach ($searchParams as $key => $val) {
        $countStmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = max(1, ceil($totalCount / $limit));

    // Get pending forms ordered by priority and date
    $sql = "SELECT
                f.form_id,
                f.form_id AS id,
                f.service_type AS Service_Type,
                f.request_type AS Request_Type,
                f.priority AS Priority,
                f.company_name AS Company_Name,
                f.client_name,
                f.contact_name AS Client_Title,
                f.email AS Email,
                f.phone AS Number_Phone,
                f.address AS Company_Address,
                f.requested_service AS Requested_Service,
                f.status,
                f.docnum,
                f.created_at,
                f.updated_at,
                f.seller AS Seller,
                f.total_cost AS PriceInput,
                f.invoice_frequency AS Invoice_Frequency,
                f.contract_duration AS Contract_Duration,
                f.include_staff AS includeStaff,
                f.inflation_adjustment AS inflationAdjustment,
                f.total_area AS totalArea,
                f.start_date_services AS startDateServices,
                f.site_observation AS Site_Observation,
                f.service_status,
                f.Work_Date,
                f.Document_Date,
                f.Order_Nomenclature,
                f.order_number,
                f.total_cost
            FROM forms f
            WHERE f.status IN ('pending', 'in_progress', 'draft')" . $searchCondition . "
            ORDER BY
                FIELD(f.status, 'draft', 'pending', 'in_progress'),
                FIELD(f.priority, 'Urgent', 'High', 'Normal', 'Low'),
                f.created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($searchParams as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check which forms have janitorial/kitchen items in contract_items
    $formIds = array_column($requests, 'form_id');
    $includeFlags = [];
    if (!empty($formIds)) {
        $placeholders = implode(',', array_fill(0, count($formIds), '?'));
        $stmtFlags = $pdo->prepare("
            SELECT form_id, category FROM contract_items
            WHERE form_id IN ($placeholders)
            GROUP BY form_id, category
        ");
        $stmtFlags->execute($formIds);
        foreach ($stmtFlags->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $includeFlags[$row['form_id']][$row['category']] = true;
        }
    }

    // Format data for display
    foreach ($requests as &$request) {
        $fid = $request['form_id'];

        // Set include flags based on contract_items
        $request['includeJanitorial'] = isset($includeFlags[$fid]['janitorial']) ? 'Yes' : 'No';
        $request['includeKitchen'] = (isset($includeFlags[$fid]['kitchen']) || isset($includeFlags[$fid]['hood_vent'])) ? 'Yes' : 'No';

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

        // Determine if this is a draft
        $request['is_draft'] = ($request['status'] === 'draft');

        // Calculate completion percentage
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

        // Service status color and label mapping
        $serviceStatus = $request['service_status'] ?? null;
        $statusMap = [
            'pending'       => ['color' => '#d97706', 'label' => 'Pending',       'label_es' => 'Pendiente',     'icon' => 'fas fa-clock'],
            'scheduled'     => ['color' => '#2563eb', 'label' => 'Scheduled',     'label_es' => 'Programado',    'icon' => 'fas fa-calendar-alt'],
            'confirmed'     => ['color' => '#7c3aed', 'label' => 'Confirmed',     'label_es' => 'Confirmado',    'icon' => 'fas fa-check-circle'],
            'in_progress'   => ['color' => '#0891b2', 'label' => 'In Progress',   'label_es' => 'En Progreso',   'icon' => 'fas fa-spinner'],
            'completed'     => ['color' => '#16a34a', 'label' => 'Completed',     'label_es' => 'Completado',    'icon' => 'fas fa-check-double'],
            'not_completed' => ['color' => '#dc2626', 'label' => 'Not Completed', 'label_es' => 'No Completado', 'icon' => 'fas fa-times-circle'],
            'cancelled'     => ['color' => '#6b7280', 'label' => 'Cancelled',     'label_es' => 'Cancelado',     'icon' => 'fas fa-ban'],
        ];

        if ($serviceStatus) {
            $statusInfo = $statusMap[$serviceStatus] ?? null;
            if ($statusInfo) {
                $request['service_status_color'] = $statusInfo['color'];
                $request['service_status_label'] = $statusInfo['label'];
                $request['service_status_label_es'] = $statusInfo['label_es'];
                $request['service_status_icon'] = $statusInfo['icon'];
            }
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
        } elseif ($serviceStatus && $serviceStatus !== 'pending' && isset($statusMap[$serviceStatus])) {
            $info = $statusMap[$serviceStatus];
            $request['status_color'] = $info['color'];
            $request['status_icon'] = '<i class="' . $info['icon'] . '"></i>';
            $request['status_label'] = $info['label'];
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
 * Calculate completion info for a form/draft
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
    if (!empty($request['PriceInput']) || !empty($request['total_cost'])) {
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
        'missing' => array_slice($missing, 0, 3)
    ];
}
?>
