<?php
/**
 * UPDATE REQUEST CONTROLLER
 * Updates form fields from the contract generator editor panel.
 * Writes to forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input)) {
        throw new Exception('No data received');
    }

    $id = $input['id'] ?? null;
    if (!$id) {
        throw new Exception('Form ID is required');
    }

    // Build dynamic UPDATE query from received fields
    $allowedFields = [
        'Service_Type' => 'service_type',
        'Request_Type' => 'request_type',
        'Priority' => 'priority',
        'Requested_Service' => 'requested_service',
        'client_name' => 'client_name',
        'Client_Title' => 'contact_name',
        'Email' => 'email',
        'Number_Phone' => 'phone',
        'Company_Name' => 'company_name',
        'Company_Address' => 'address',
        'City' => 'city',
        'State' => 'state',
        'Is_New_Client' => 'is_new_client',
        'Site_Visit_Conducted' => 'site_visit_conducted',
        'Invoice_Frequency' => 'invoice_frequency',
        'Contract_Duration' => 'contract_duration',
        'Seller' => 'seller',
        'PriceInput' => 'grand_total',
        'inflationAdjustment' => 'inflation_adjustment',
        'totalArea' => 'total_area',
        'buildingsIncluded' => 'buildings_included',
        'startDateServices' => 'start_date_services',
        'Site_Observation' => 'site_observation',
        'Additional_Comments' => 'additional_comments',
        'includeStaff' => 'include_staff',
        'Document_Date' => 'Document_Date',
        'Work_Date' => 'Work_Date',
        'status' => 'status',
        'service_status' => 'service_status',
    ];

    $setClauses = [];
    $params = [':id' => $id];

    foreach ($input as $key => $value) {
        if ($key === 'id') continue;
        if (isset($allowedFields[$key])) {
            $dbField = $allowedFields[$key];
            $paramName = ':' . $dbField;
            $setClauses[] = "`{$dbField}` = {$paramName}";
            $params[$paramName] = $value;
        }
    }

    if (empty($setClauses)) {
        throw new Exception('No valid fields to update');
    }

    // Always update timestamp
    $setClauses[] = "updated_at = NOW()";

    $sql = "UPDATE forms SET " . implode(', ', $setClauses) . " WHERE form_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Form updated successfully',
        'updated_fields' => count($setClauses) - 1
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
