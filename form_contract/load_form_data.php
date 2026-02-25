<?php
// ============================================================
// load_form_data.php - Load Form Data
// ============================================================
// Reads from forms + contract_items (unified architecture)
// ============================================================

header('Content-Type: application/json');

// Incluir configuracion de base de datos y RBAC
require_once 'db_config.php';
require_once 'order_access.php';

try {
    // Enforce authentication + module access
    $currentUser = requireOrderAccess();

    $form_id = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;

    if (!$form_id) {
        throw new Exception('Form ID is required');
    }

    $pdo = getDBConnection();

    // RBAC: Verify the user has access to this specific order
    if (!canAccessOrder($pdo, $form_id, $currentUser)) {
        denyOrderAccess();
    }

    // Cargar datos del formulario
    $sql = "SELECT * FROM forms WHERE form_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$form_id]);
    $form = $stmt->fetch();

    if (!$form) {
        throw new Exception('Form not found');
    }

    // Cargar contract items (unified) and split by category for frontend compatibility
    $sql_items = "SELECT * FROM contract_items WHERE form_id = ? ORDER BY category, position";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$form_id]);
    $all_items = $stmt_items->fetchAll();

    // Split items by category for backward compatibility with frontend
    $janitorial_costs = [];
    $kitchen_costs = [];
    $hood_costs = [];
    foreach ($all_items as $item) {
        switch ($item['category']) {
            case 'janitorial':
                $janitorial_costs[] = $item;
                break;
            case 'kitchen':
                $kitchen_costs[] = $item;
                break;
            case 'hood_vent':
                $hood_costs[] = $item;
                break;
        }
    }

    // Cargar scope sections (dynamic blocks)
    $sql_scope_sections = "SELECT title, scope_content FROM scope_sections WHERE form_id = ? ORDER BY section_order ASC";
    $stmt_scope_sections = $pdo->prepare($sql_scope_sections);
    $stmt_scope_sections->execute([$form_id]);
    $scope_sections = $stmt_scope_sections->fetchAll();

    // Cargar contract staff
    $sql_staff = "SELECT * FROM contract_staff WHERE form_id = ? ORDER BY id";
    $stmt_staff = $pdo->prepare($sql_staff);
    $stmt_staff->execute([$form_id]);
    $contract_staff = $stmt_staff->fetchAll();

    // Cargar fotos
    $sql_photos = "SELECT * FROM form_photos WHERE form_id = ?";
    $stmt_photos = $pdo->prepare($sql_photos);
    $stmt_photos->execute([$form_id]);
    $photos = $stmt_photos->fetchAll();

    // ============================================================
    // Map database fields to form field names
    // ============================================================
    $formData = [
        // Section 1: Request Information
        'Service_Type' => $form['service_type'],
        'Request_Type' => $form['request_type'],
        'Priority' => $form['priority'],
        'Requested_Service' => $form['requested_service'],

        // Section 2: Client Information
        'Client_Name' => $form['client_name'],
        'Client_Title' => $form['contact_name'],
        'Number_Phone' => $form['phone'],
        'Email' => $form['email'],
        'Company_Name' => $form['company_name'],
        'Company_Address' => $form['address'],
        'City' => $form['city'],
        'State' => $form['state'],
        'Is_New_Client' => $form['is_new_client'],

        // Section 3: Operational Details
        'Site_Visit_Conducted' => $form['site_visit_conducted'],
        'Invoice_Frequency' => $form['invoice_frequency'],
        'Contract_Duration' => $form['contract_duration'],

        // Section 4: Economic Information
        'Seller' => $form['seller'],
        'PriceInput' => $form['total_cost'],
        'payment_terms' => $form['payment_terms'],
        'includeStaff' => $form['include_staff'],

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
        'order_number' => $form['order_number'],

        // Section 10: Service Status
        'service_status' => $form['service_status'],

        // Contract workflow status (draft, pending, completed, etc.)
        'form_status' => $form['status'],
    ];

    // Determine if the current user can edit this form
    // Completed contracts can only be edited by Admin (1) or Leader (2)
    $canEdit = true;
    if ($form['status'] === 'completed') {
        $canEdit = in_array((int) $currentUser['role_id'], RBAC_FULL_ACCESS_ROLES, true);
    }

    echo json_encode([
        'success' => true,
        'form' => $formData,
        'can_edit' => $canEdit,
        'scope_sections' => $scope_sections,
        'contract_items' => $all_items,
        'kitchen_costs' => $kitchen_costs,
        'hood_costs' => $hood_costs,
        'janitorial_costs' => $janitorial_costs,
        'contract_staff' => $contract_staff,
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
