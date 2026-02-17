<?php
/**
 * get_service_detail.php
 * Returns detailed information about a specific form/service.
 * Reads from forms + contract_items (single source of truth).
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $request_id = $_GET['id'] ?? null;

    if (!$request_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing request ID'
        ]);
        exit;
    }

    $pdo = getDBConnection();

    // Get form data (single source of truth)
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE form_id = :id");
    $stmt->execute([':id' => $request_id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        echo json_encode([
            'success' => false,
            'message' => 'Request not found'
        ]);
        exit;
    }

    // Build response with aliased field names for frontend compatibility
    $request = [
        'id' => $form['form_id'],
        'form_id' => $form['form_id'],
        'Service_Type' => $form['service_type'],
        'Request_Type' => $form['request_type'],
        'Priority' => $form['priority'],
        'Requested_Service' => $form['requested_service'],
        'client_name' => $form['client_name'],
        'Client_Title' => $form['contact_name'],
        'Email' => $form['email'],
        'Number_Phone' => $form['phone'],
        'Company_Name' => $form['company_name'],
        'Company_Address' => $form['address'],
        'City' => $form['city'],
        'State' => $form['state'],
        'Seller' => $form['seller'],
        'PriceInput' => $form['grand_total'],
        'Invoice_Frequency' => $form['invoice_frequency'],
        'Contract_Duration' => $form['contract_duration'],
        'inflationAdjustment' => $form['inflation_adjustment'],
        'totalArea' => $form['total_area'],
        'buildingsIncluded' => $form['buildings_included'],
        'startDateServices' => $form['start_date_services'],
        'Site_Observation' => $form['site_observation'],
        'Additional_Comments' => $form['additional_comments'],
        'Document_Date' => $form['Document_Date'],
        'Work_Date' => $form['Work_Date'],
        'Order_Nomenclature' => $form['Order_Nomenclature'],
        'order_number' => $form['order_number'],
        'docnum' => $form['docnum'],
        'status' => $form['status'],
        'service_status' => $form['service_status'],
        'task_tracking' => $form['task_tracking'],
        'admin_notes' => $form['admin_notes'],
        'ready_to_invoice' => $form['ready_to_invoice'],
        'final_pdf_path' => $form['final_pdf_path'],
        'created_at' => $form['created_at'],
        'updated_at' => $form['updated_at'],
        'completed_at' => $form['completed_at'],
    ];

    // Get contract items
    $stmtItems = $pdo->prepare("SELECT * FROM contract_items WHERE form_id = ? ORDER BY service_category, service_number");
    $stmtItems->execute([$request_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Get scope of work
    $stmtScope = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
    $stmtScope->execute([$request_id]);
    $scopeTasks = $stmtScope->fetchAll(PDO::FETCH_COLUMN);
    $request['Scope_Of_Work'] = $scopeTasks;

    // Parse task_tracking JSON
    if (!empty($request['task_tracking'])) {
        $decoded = json_decode($request['task_tracking'], true);
        if ($decoded !== null) {
            $request['task_tracking_parsed'] = $decoded;
        }
    }

    echo json_encode([
        'success' => true,
        'request' => $request,
        'contract_items' => $items
    ]);

} catch (Exception $e) {
    error_log("Error getting service detail: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading service: ' . $e->getMessage()
    ]);
}
?>
