<?php
/**
 * GENERATE PDF CONTROLLER
 * Generates PDFs based on templates using form data.
 * Reads from forms + contract_items (single source of truth).
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../config/db_config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Get form ID from request
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('Form ID is required');
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
    $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Split by category for template compatibility
    $janitorialServices = [];
    $kitchenServices = [];
    $hoodVentServices = [];
    foreach ($allItems as $item) {
        switch ($item['category']) {
            case 'janitorial': $janitorialServices[] = $item; break;
            case 'kitchen': $kitchenServices[] = $item; break;
            case 'hood_vent': $hoodVentServices[] = $item; break;
        }
    }

    // Get scope of work
    $stmtS = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
    $stmtS->execute([$id]);
    $scopeOfWorkTasks = $stmtS->fetchAll(PDO::FETCH_COLUMN);

    // Get scope sections
    $stmtSS = $pdo->prepare("SELECT title, scope_content FROM scope_sections WHERE form_id = ? ORDER BY section_order ASC");
    $stmtSS->execute([$id]);
    $scopeSections = $stmtSS->fetchAll(PDO::FETCH_ASSOC);

    // Build $data array compatible with templates
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
        'Seller' => $form['seller'],
        'PriceInput' => $form['total_cost'],
        'Invoice_Frequency' => $form['invoice_frequency'],
        'Contract_Duration' => $form['contract_duration'],
        'inflationAdjustment' => $form['inflation_adjustment'],
        'totalArea' => $form['total_area'],
        'buildingsIncluded' => $form['buildings_included'],
        'startDateServices' => $form['start_date_services'],
        'Site_Observation' => $form['site_observation'],
        'Additional_Comments' => $form['additional_comments'],
        'Scope_Of_Work' => $scopeOfWorkTasks,
        'status' => $form['status'],
        'docnum' => $form['docnum'] ?? $form['Order_Nomenclature'],
        'Document_Date' => $form['Document_Date'],
        'Work_Date' => $form['Work_Date'],
        'Order_Nomenclature' => $form['Order_Nomenclature'],
        'order_number' => $form['order_number'],
        'total_cost' => $form['total_cost'],
    ];

    // Determine template
    $request_type = strtolower($data['Request_Type'] ?? 'quote');
    $template_file = __DIR__ . "/../templates/{$request_type}.php";

    if (!file_exists($template_file)) {
        throw new Exception("Template not found for type: {$request_type}");
    }

    // Render template
    ob_start();
    include $template_file;
    $html = ob_get_clean();

    // If preview mode, return HTML directly
    if (isset($_GET['preview']) && $_GET['preview'] === '1') {
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }

    // Generate PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output PDF
    $company_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['Company_Name'] ?? 'Document');
    $docnum = $data['docnum'] ?? 'DRAFT';
    $filename = strtoupper($request_type) . "_{$docnum}_{$company_safe}.pdf";

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $dompdf->output();

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
