<?php
/**
 * GENERATE VENT HOOD REPORT PDF CONTROLLER
 * Generates PDF for Vent Hood Service Reports.
 * Reads from forms + contract_items (single source of truth).
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/init.php';
$pdo = Database::getConnection();

use Dompdf\Dompdf;
use Dompdf\Options;

try {
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

    // Get hood vent services from contract_items
    $stmtItems = $pdo->prepare("SELECT * FROM contract_items WHERE form_id = ? AND category = 'hood_vent' ORDER BY position");
    $stmtItems->execute([$id]);
    $hoodVentServices = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Get scope of work
    $stmtS = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
    $stmtS->execute([$id]);
    $scopeOfWorkTasks = $stmtS->fetchAll(PDO::FETCH_COLUMN);

    // Build $data for template
    $data = [
        'Company_Name' => $form['company_name'],
        'client_name' => $form['client_name'],
        'Company_Address' => $form['address'],
        'Number_Phone' => $form['phone'],
        'Email' => $form['email'],
        'Seller' => $form['seller'],
        'docnum' => $form['docnum'] ?? $form['Order_Nomenclature'],
        'Document_Date' => $form['Document_Date'],
        'Work_Date' => $form['Work_Date'],
        'status' => $form['status'],
        'Invoice_Frequency' => $form['invoice_frequency'],
        'Contract_Duration' => $form['contract_duration'],
        'total_cost' => $form['total_cost'],
        'Order_Nomenclature' => $form['Order_Nomenclature'],
        'order_number' => $form['order_number'],
    ];

    // Render report template
    $template_file = __DIR__ . '/../templates/vent_hood_report.php';

    if (!file_exists($template_file)) {
        throw new Exception('Vent hood report template not found');
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
    $filename = "VENT_HOOD_REPORT_{$company_safe}.pdf";

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
