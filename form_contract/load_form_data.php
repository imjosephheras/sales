<?php
// ============================================================
// load_form_data.php - VERSIÓN CORREGIDA (v2.0)
// Fecha: 2026-01-05
// Fix: Agregados campos nuevos y corregidos nombres de campos
// ============================================================

header('Content-Type: application/json');

// Incluir configuración de base de datos
require_once 'db_config.php';

try {
    $form_id = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;
    
    if (!$form_id) {
        throw new Exception('Form ID is required');
    }
    
    $pdo = getDBConnection();
    
    // Cargar datos del formulario
    $sql = "SELECT * FROM forms WHERE form_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();
    
    if (!$form) {
        throw new Exception('Form not found');
    }
    
    // Cargar scope of work
    $sql_scope = "SELECT task_name FROM scope_of_work WHERE form_id = ?";
    $stmt_scope = $pdo->prepare($sql_scope);
    $stmt_scope->execute([$form_id]);
    $scope_tasks = $stmt_scope->fetchAll(PDO::FETCH_COLUMN);
    
    // Cargar kitchen cleaning costs
    $sql_kitchen = "SELECT * FROM kitchen_cleaning_costs WHERE form_id = ? ORDER BY service_number";
    $stmt_kitchen = $pdo->prepare($sql_kitchen);
    $stmt_kitchen->execute([$form_id]);
    $kitchen_costs = $stmt_kitchen->fetchAll();
    
    // Cargar hood vent costs
    $sql_hood = "SELECT * FROM hood_vent_costs WHERE form_id = ? ORDER BY service_number";
    $stmt_hood = $pdo->prepare($sql_hood);
    $stmt_hood->execute([$form_id]);
    $hood_costs = $stmt_hood->fetchAll();
    
    // Cargar janitorial services costs (Q18) ✅ NUEVO
    $sql_janitorial = "SELECT * FROM janitorial_services_costs WHERE form_id = ? ORDER BY service_number";
    $stmt_janitorial = $pdo->prepare($sql_janitorial);
    $stmt_janitorial->execute([$form_id]);
    $janitorial_costs = $stmt_janitorial->fetchAll();
    
    // Cargar fotos
    $sql_photos = "SELECT * FROM form_photos WHERE form_id = ?";
    $stmt_photos = $pdo->prepare($sql_photos);
    $stmt_photos->execute([$form_id]);
    $photos = $stmt_photos->fetchAll();
    
    // ============================================================
    // Map database fields to form field names (NOMBRES CORREGIDOS)
    // ============================================================
    $formData = [
        // Section 1: Request Information
        'Service_Type' => $form['service_type'],
        'Request_Type' => $form['request_type'],
        'Priority' => $form['priority'],
        'Requested_Service' => $form['requested_service'],
        
        // Section 2: Client Information (⚠️ NOMBRES CORREGIDOS)
        'Client_Name' => $form['client_name'],
        'Client_Title' => $form['contact_name'],           // ✅ CORREGIDO (era Contact_Name)
        'Number_Phone' => $form['phone'],                  // ✅ CORREGIDO (era Phone)
        'Email' => $form['email'],
        'Company_Name' => $form['company_name'],           // ✅ NUEVO
        'Company_Address' => $form['address'],             // ✅ CORREGIDO (era Address)
        'City' => $form['city'],
        'State' => $form['state'],
        'Is_New_Client' => $form['is_new_client'],         // ✅ NUEVO
        
        // Section 3: Operational Details
        'Site_Visit_Conducted' => $form['site_visit_conducted'],
        'Invoice_Frequency' => $form['invoice_frequency'],
        'Contract_Duration' => $form['contract_duration'],
        
        // Section 4: Economic Information (⚠️ CAMPOS NUEVOS)
        'Seller' => $form['seller'],                       // ✅ NUEVO (Q16)
        'PriceInput' => $form['total_cost'],               // ✅ CORREGIDO (era total_cost)
        'payment_terms' => $form['payment_terms'],
        'includeStaff' => $form['include_staff'],          // ✅ NUEVO (Q20)
        
        // Section 5: Contract Information
        'inflationAdjustment' => $form['inflation_adjustment'],
        'totalArea' => $form['total_area'],
        'buildingsIncluded' => $form['buildings_included'],
        'startDateServices' => $form['start_date_services'],
        
        // Section 6: Observations
        'Site_Observation' => $form['site_observation'],
        'Additional_Comments' => $form['additional_comments'],
        'Email_Information_Sent' => $form['email_information_sent'],

        // Section 9: Document & Work Dates
        'Document_Date' => $form['Document_Date'],
        'Work_Date' => $form['Work_Date'],
        'Order_Nomenclature' => $form['Order_Nomenclature'],
    ];
    
    echo json_encode([
        'success' => true,
        'form' => $formData,
        'scope_tasks' => $scope_tasks,
        'kitchen_costs' => $kitchen_costs,
        'hood_costs' => $hood_costs,
        'janitorial_costs' => $janitorial_costs,           // ✅ NUEVO
        'photos' => $photos
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>