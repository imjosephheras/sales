<?php
/**
 * UPDATE REQUEST CONTROLLER
 * Updates form fields from the contract generator editor panel.
 * Writes to forms table (single source of truth).
 * Also handles contract_items and contract_staff updates
 * from the editable Section 4 tables in the editor.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/init.php';
$pdo = Database::getConnection();

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
        'PriceInput' => 'total_cost',
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
        // Skip array fields handled separately below
        if (in_array($key, ['contract_items_janitorial', 'contract_items_kitchen', 'contract_staff'])) continue;
        if (isset($allowedFields[$key])) {
            $dbField = $allowedFields[$key];
            $paramName = ':' . $dbField;
            $setClauses[] = "`{$dbField}` = {$paramName}";
            $params[$paramName] = $value;
        }
    }

    // Begin transaction for atomicity
    $pdo->beginTransaction();

    // Update forms table if there are fields to update
    if (!empty($setClauses)) {
        $setClauses[] = "updated_at = NOW()";
        $sql = "UPDATE forms SET " . implode(', ', $setClauses) . " WHERE form_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    // ========================================
    // Save contract_items (Sections 18 & 19)
    // Delete-and-reinsert strategy per category
    // ========================================

    $janitorialItems = $input['contract_items_janitorial'] ?? null;
    $kitchenItems = $input['contract_items_kitchen'] ?? null;

    if (is_array($janitorialItems)) {
        // Delete existing janitorial items for this form
        $pdo->prepare("DELETE FROM contract_items WHERE form_id = ? AND category = 'janitorial'")
            ->execute([$id]);

        // Insert updated rows
        $insertStmt = $pdo->prepare("
            INSERT INTO contract_items (form_id, category, service_type, service_time, frequency, description, subtotal, position)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($janitorialItems as $pos => $item) {
            $insertStmt->execute([
                $id,
                'janitorial',
                $item['service_type'] ?? null,
                $item['service_time'] ?? null,
                $item['frequency'] ?? null,
                $item['description'] ?? null,
                !empty($item['subtotal']) ? $item['subtotal'] : null,
                $pos
            ]);
        }
    }

    if (is_array($kitchenItems)) {
        // Delete existing kitchen and hood_vent items for this form
        $pdo->prepare("DELETE FROM contract_items WHERE form_id = ? AND category IN ('kitchen', 'hood_vent')")
            ->execute([$id]);

        // Insert updated rows (preserving original category)
        $insertStmt = $pdo->prepare("
            INSERT INTO contract_items (form_id, category, service_type, service_time, frequency, description, subtotal, position)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($kitchenItems as $pos => $item) {
            $category = isset($item['category']) && $item['category'] === 'hood_vent' ? 'hood_vent' : 'kitchen';
            $insertStmt->execute([
                $id,
                $category,
                $item['service_type'] ?? null,
                $item['service_time'] ?? null,
                $item['frequency'] ?? null,
                $item['description'] ?? null,
                !empty($item['subtotal']) ? $item['subtotal'] : null,
                $pos
            ]);
        }
    }

    // ========================================
    // Save contract_staff (Section 20)
    // ========================================

    $staffItems = $input['contract_staff'] ?? null;

    if (is_array($staffItems)) {
        // Delete existing staff for this form
        $pdo->prepare("DELETE FROM contract_staff WHERE form_id = ?")->execute([$id]);

        // Insert updated rows
        $insertStmt = $pdo->prepare("
            INSERT INTO contract_staff (form_id, position, base_rate, percent_increase, bill_rate)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($staffItems as $item) {
            $insertStmt->execute([
                $id,
                $item['position'] ?? null,
                !empty($item['base_rate']) ? $item['base_rate'] : null,
                !empty($item['percent_increase']) ? $item['percent_increase'] : null,
                !empty($item['bill_rate']) ? $item['bill_rate'] : null
            ]);
        }
    }

    // Recalculate total_cost from contract_items
    $stmtTotal = $pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM contract_items WHERE form_id = ?");
    $stmtTotal->execute([$id]);
    $newTotal = $stmtTotal->fetchColumn();
    if ($newTotal > 0) {
        $pdo->prepare("UPDATE forms SET total_cost = ? WHERE form_id = ?")->execute([$newTotal, $id]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Form updated successfully',
        'updated_fields' => count($setClauses) > 0 ? count($setClauses) - 1 : 0
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
