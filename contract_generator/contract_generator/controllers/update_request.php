<?php
/**
 * UPDATE REQUEST CONTROLLER
 * Guarda los cambios realizados en el editor
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Obtener datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }

    $id = $input['request_id'] ?? null;
    
    if (!$id) {
        throw new Exception('Request ID is required');
    }

    // Campos a actualizar (excluyendo Priority Q3, Email_Information_Sent Q27, photos Q29)
    $fields = [
        // Section 1: Request Information
        'Service_Type',
        'Request_Type',
        'Requested_Service',
        'Seller',

        // Section 2: Client Information
        'client_name',
        'Client_Title',
        'Email',
        'Number_Phone',
        'Company_Name',
        'Company_Address',
        'Is_New_Client',

        // Section 3: Operational Details
        'Site_Visit_Conducted',
        'Invoice_Frequency',
        'Contract_Duration',

        // Section 4: Economic Information
        'PriceInput',
        'Prime_Quoted_Price',
        'Total_Price',
        'Currency',

        // Section 5: Contract Information
        'inflationAdjustment',
        'totalArea',
        'buildingsIncluded',
        'startDateServices',

        // Section 6: Observations
        'Site_Observation',
        'Additional_Comments'
    ];

    // Construir query dinámica
    $updates = [];
    $params = [':id' => $id];

    foreach ($fields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $input[$field];
        }
    }

    if (empty($updates)) {
        throw new Exception('No fields to update');
    }

    // Añadir timestamp de actualización
    $updates[] = "updated_at = NOW()";

    // Ejecutar update
    $sql = "UPDATE requests SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Verificar si se actualizó
    if ($stmt->rowCount() === 0) {
        throw new Exception('No changes made or request not found');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Request updated successfully',
        'updated_fields' => count($updates) - 1 // -1 por el updated_at
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>