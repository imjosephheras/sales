<?php
/**
 * UNIVERSAL SERVICE REPORT PDF CONTROLLER
 * ========================================
 * Generates PDF (or HTML preview) for ANY service type using
 * the universal_service_report.php template and service_report_config.php.
 *
 * Usage:
 *   generate_service_report.php?id=123&service_type=kitchen_exhaust_cleaning
 *   generate_service_report.php?id=123&service_type=roof_exterior&preview=1
 *
 * Parameters:
 *   id           - Form ID (required)
 *   service_type - Config key from service_report_config.php (required)
 *   preview      - If "1", returns HTML instead of PDF (optional)
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/db_config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $id = $_GET['id'] ?? null;
    $serviceTypeKey = $_GET['service_type'] ?? null;

    if (!$id) {
        throw new Exception('Form ID is required. Usage: ?id=123&service_type=kitchen_exhaust_cleaning');
    }

    if (!$serviceTypeKey) {
        throw new Exception('Service type is required. Usage: ?id=123&service_type=kitchen_exhaust_cleaning');
    }

    // Load service type configurations
    $allConfigs = require __DIR__ . '/../config/service_report_config.php';

    if (!isset($allConfigs[$serviceTypeKey])) {
        $available = implode(', ', array_keys($allConfigs));
        throw new Exception("Unknown service type: '{$serviceTypeKey}'. Available types: {$available}");
    }

    $serviceConfig = $allConfigs[$serviceTypeKey];

    // Get form data from database
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE form_id = :id");
    $stmt->execute([':id' => $id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        throw new Exception('Form not found');
    }

    // Build $data array for the template
    $data = [
        'Company_Name'    => $form['company_name'],
        'Client_Name'     => $form['client_name'],
        'Company_Address'  => $form['address'],
        'Number_Phone'     => $form['phone'],
        'Email'            => $form['email'],
        'Seller'           => $form['seller'],
        'docnum'           => $form['docnum'] ?? $form['Order_Nomenclature'] ?? '',
        'Document_Date'    => $form['Document_Date'],
        'Work_Date'        => $form['Work_Date'],
        'status'           => $form['status'],
        'Service_Type'     => $form['service_type'],
        'Invoice_Frequency' => $form['invoice_frequency'],
        'Contract_Duration' => $form['contract_duration'],
        'total_cost'       => $form['total_cost'],
        'Order_Nomenclature' => $form['Order_Nomenclature'] ?? '',
        'order_number'     => $form['order_number'] ?? '',
    ];

    // Render the universal template
    $template_file = __DIR__ . '/../templates/universal_service_report.php';

    if (!file_exists($template_file)) {
        throw new Exception('Universal service report template not found');
    }

    ob_start();
    include $template_file;
    $html = ob_get_clean();

    // If preview mode, return HTML directly
    if (isset($_GET['preview']) && $_GET['preview'] === '1') {
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }

    // Generate PDF with DomPDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    // Build filename from service type title
    $company_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['Company_Name'] ?? 'Document');
    $type_safe    = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtoupper($serviceConfig['title']));
    $filename     = "SERVICE_REPORT_{$type_safe}_{$company_safe}.pdf";

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $dompdf->output();

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
