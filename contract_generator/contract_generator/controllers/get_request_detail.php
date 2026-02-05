<?php
/**
 * GET REQUEST DETAIL CONTROLLER
 * Devuelve todos los datos de una solicitud específica
 * Excluye: Priority (Q3), Email_Information_Sent (Q27), photos (Q29)
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        throw new Exception('Request ID is required');
    }

    // Obtener datos completos - seleccionando campos específicos
    // Excluimos: Priority, Email_Information_Sent, photos
    $stmt = $pdo->prepare("
        SELECT
            id, status, docnum, created_at, updated_at, completed_at,
            form_id,

            -- Section 1: Request Information
            Service_Type, Request_Type, Priority, Requested_Service,

            -- Section 2: Client Information
            client_name, Client_Title, Email, Number_Phone,
            Company_Name, Company_Address, Is_New_Client,

            -- Section 3: Operational Details
            Site_Visit_Conducted, frequency_period, week_days, one_time,
            Invoice_Frequency, Contract_Duration,

            -- Section 4: Economic Information
            Seller, PriceInput, Prime_Quoted_Price,

            -- Janitorial Services (Q18)
            includeJanitorial, type18, write18, time18, freq18, desc18,
            subtotal18, total18, taxes18, grand18,

            -- Kitchen/Hood Services (Q19)
            includeKitchen, type19, time19, freq19, desc19,
            subtotal19, total19, taxes19, grand19,

            -- Staff (Q20)
            includeStaff, base_staff, increase_staff, bill_staff,

            -- Section 5: Contract Information
            inflationAdjustment, totalArea, buildingsIncluded, startDateServices,

            -- Section 6: Observations (excluding Email_Information_Sent Q27)
            Site_Observation, Additional_Comments,

            -- Section 7: Scope of Work
            Scope_Of_Work,

            -- Document metadata
            document_type, document_number

        FROM requests
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request not found');
    }

    // Add Client_Name alias for compatibility with form_contract
    if (isset($request['client_name'])) {
        $request['Client_Name'] = $request['client_name'];
    }

    // Decodificar campos JSON
    if (!empty($request['Scope_Of_Work'])) {
        $request['Scope_Of_Work'] = json_decode($request['Scope_Of_Work'], true);
    }

    // Decodificar arrays de servicios (si están almacenados como JSON)
    $jsonFields = [
        'type18', 'write18', 'time18', 'freq18', 'desc18', 'subtotal18',
        'type19', 'time19', 'freq19', 'desc19', 'subtotal19',
        'base_staff', 'increase_staff', 'bill_staff', 'week_days'
    ];

    foreach ($jsonFields as $field) {
        if (!empty($request[$field])) {
            $decoded = json_decode($request[$field], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request[$field] = $decoded;
            }
        }
    }

    // Query service detail tables from form database if form_id exists
    $formId = $request['form_id'] ?? null;

    // Also try to find form_id by docnum if not directly set
    if (!$formId && !empty($request['docnum'])) {
        $stmtForm = $pdo->prepare("SELECT form_id FROM forms WHERE Order_Nomenclature = ? LIMIT 1");
        $stmtForm->execute([$request['docnum']]);
        $formRow = $stmtForm->fetch(PDO::FETCH_ASSOC);
        if ($formRow) {
            $formId = $formRow['form_id'];
        }
    }

    $request['janitorial_services'] = [];
    $request['kitchen_services'] = [];
    $request['hood_vent_services'] = [];
    $request['scope_of_work_tasks'] = [];

    if ($formId) {
        $request['form_id'] = $formId;

        // Get janitorial services from detail table
        $stmtJ = $pdo->prepare("SELECT * FROM janitorial_services_costs WHERE form_id = ? ORDER BY service_number");
        $stmtJ->execute([$formId]);
        $request['janitorial_services'] = $stmtJ->fetchAll(PDO::FETCH_ASSOC);

        // Get kitchen cleaning services from detail table
        $stmtK = $pdo->prepare("SELECT * FROM kitchen_cleaning_costs WHERE form_id = ? ORDER BY service_number");
        $stmtK->execute([$formId]);
        $request['kitchen_services'] = $stmtK->fetchAll(PDO::FETCH_ASSOC);

        // Get hood vent services from detail table
        $stmtH = $pdo->prepare("SELECT * FROM hood_vent_costs WHERE form_id = ? ORDER BY service_number");
        $stmtH->execute([$formId]);
        $request['hood_vent_services'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        // Get scope of work tasks from detail table
        $stmtS = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
        $stmtS->execute([$formId]);
        $request['scope_of_work_tasks'] = $stmtS->fetchAll(PDO::FETCH_COLUMN);
    }

    // Formatear fechas
    if (!empty($request['created_at'])) {
        $request['created_at_formatted'] = date('M d, Y g:i A', strtotime($request['created_at']));
    }

    if (!empty($request['startDateServices'])) {
        $request['startDateServices_formatted'] = date('Y-m-d', strtotime($request['startDateServices']));
    }

    if (!empty($request['completed_at'])) {
        $request['completed_at_formatted'] = date('M d, Y g:i A', strtotime($request['completed_at']));
    }

    echo json_encode([
        'success' => true,
        'data' => $request
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>