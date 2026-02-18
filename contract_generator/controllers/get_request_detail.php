<?php
/**
 * GET REQUEST DETAIL CONTROLLER
 * Returns full data for a single form (contract) with its contract_items.
 * Reads from forms + contract_items (single source of truth).
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (!$id) {
        throw new Exception('ID is required');
    }

    // Get form data
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE form_id = :id");
    $stmt->execute([':id' => $id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        throw new Exception('Form not found');
    }

    // Get contract items
    $stmtItems = $pdo->prepare("SELECT * FROM contract_items WHERE form_id = ? ORDER BY category, position");
    $stmtItems->execute([$id]);
    $contractItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Split contract items by category (same logic as generate_pdf.php)
    $janitorialServices = [];
    $kitchenServices = [];
    $hoodVentServices = [];
    foreach ($contractItems as $item) {
        switch ($item['category']) {
            case 'janitorial': $janitorialServices[] = $item; break;
            case 'kitchen': $kitchenServices[] = $item; break;
            case 'hood_vent': $hoodVentServices[] = $item; break;
        }
    }

    // Get scope of work
    $stmtScope = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
    $stmtScope->execute([$id]);
    $scopeOfWork = $stmtScope->fetchAll(PDO::FETCH_COLUMN);

    // Get scope sections
    $stmtSections = $pdo->prepare("SELECT title, scope_content FROM scope_sections WHERE form_id = ? ORDER BY section_order ASC");
    $stmtSections->execute([$id]);
    $scopeSections = $stmtSections->fetchAll(PDO::FETCH_ASSOC);

    // Map form fields to the response format expected by the contract generator frontend
    $data = [
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
        'Is_New_Client' => $form['is_new_client'],
        'Site_Visit_Conducted' => $form['site_visit_conducted'],
        'Invoice_Frequency' => $form['invoice_frequency'],
        'Contract_Duration' => $form['contract_duration'],
        'Seller' => $form['seller'],
        'PriceInput' => $form['total_cost'],
        'includeStaff' => $form['include_staff'],
        'inflationAdjustment' => $form['inflation_adjustment'],
        'totalArea' => $form['total_area'],
        'buildingsIncluded' => $form['buildings_included'],
        'startDateServices' => $form['start_date_services'],
        'Site_Observation' => $form['site_observation'],
        'Additional_Comments' => $form['additional_comments'],
        'Scope_Of_Work' => $scopeOfWork,
        'status' => $form['status'],
        'docnum' => $form['docnum'] ?? $form['Order_Nomenclature'],
        'Document_Date' => $form['Document_Date'],
        'Work_Date' => $form['Work_Date'],
        'Order_Nomenclature' => $form['Order_Nomenclature'],
        'order_number' => $form['order_number'],
        'service_status' => $form['service_status'],
        'total_cost' => $form['total_cost'],
        'created_at' => $form['created_at'],
        'updated_at' => $form['updated_at'],
        'completed_at' => $form['completed_at'],
        'contract_items' => $contractItems,
        'janitorial_services' => $janitorialServices,
        'kitchen_services' => $kitchenServices,
        'hood_vent_services' => $hoodVentServices,
        'scope_sections' => $scopeSections,
    ];

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
