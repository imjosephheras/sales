<?php
/**
 * save_timesheet_pdf.php
 * Receives timesheet HTML content, generates a PDF with dompdf,
 * saves it to storage, and links it to the billing module
 * for the corresponding Work Order.
 */
require_once __DIR__ . '/../app/bootstrap.php';
Middleware::auth();

require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $form_id       = $input['form_id'] ?? null;
    $html_content  = $input['html_content'] ?? null;
    $timesheet_type = $input['timesheet_type'] ?? 2;

    if (!$form_id) {
        echo json_encode(['success' => false, 'error' => 'No Work Order selected. Please load a form first.']);
        exit;
    }

    if (!$html_content) {
        echo json_encode(['success' => false, 'error' => 'No timesheet content to save.']);
        exit;
    }

    $pdo = getDBConnection();

    // Load the form to get order_number and other info
    $stmt = $pdo->prepare("SELECT form_id, order_number, client_name, company_name, request_type, requested_service, total_cost FROM forms WHERE form_id = ?");
    $stmt->execute([(int)$form_id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        echo json_encode(['success' => false, 'error' => 'Work Order not found.']);
        exit;
    }

    $order_number = $form['order_number'] ?? 'WO-' . $form_id;

    // Encode logo as base64 for embedding in PDF
    $logo_path = __DIR__ . '/Images/Facility.png';
    $logo_base64 = '';
    if (file_exists($logo_path)) {
        $logo_data = base64_encode(file_get_contents($logo_path));
        $logo_base64 = 'data:image/png;base64,' . $logo_data;
    }

    // Build full HTML document for dompdf
    $typeLabel = ($timesheet_type == 1) ? 'WEEKLY TIME SHEET' : 'DAILY TIME SHEET';
    $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 12px;
        color: #333;
        margin: 20px;
    }
    .ts-company-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 3px solid #001f54;
    }
    .ts-logo {
        height: 50px;
        margin-right: 15px;
    }
    .ts-company-info h2 {
        font-size: 18px;
        color: #001f54;
        margin: 0;
    }
    .ts-company-info h3 {
        font-size: 13px;
        color: #555;
        margin: 3px 0 0 0;
    }
    .ts-info-row {
        margin-bottom: 12px;
    }
    .ts-info-row .field {
        display: inline-block;
        margin-right: 30px;
        margin-bottom: 5px;
    }
    .ts-info-row .field label {
        font-weight: bold;
        color: #001f54;
        font-size: 11px;
    }
    .ts-info-row .field span {
        font-size: 12px;
    }
    table.ts-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    table.ts-table th {
        background-color: #001f54;
        color: #fff;
        padding: 8px 5px;
        font-size: 10px;
        text-transform: uppercase;
        text-align: center;
    }
    table.ts-table td {
        padding: 6px 4px;
        text-align: center;
        border-bottom: 1px solid #ddd;
        font-size: 11px;
    }
    table.ts-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .ts-total-row td {
        background-color: #001f54 !important;
        color: #fff !important;
        font-weight: bold;
        font-size: 13px;
        padding: 10px 5px;
    }
    .ts-total-cell {
        color: #ffd700 !important;
        font-size: 15px !important;
    }
    .ts-signatures {
        margin-top: 30px;
        padding-top: 15px;
    }
    .ts-sig-block {
        display: inline-block;
        width: 45%;
        text-align: center;
        margin-right: 4%;
    }
    .ts-sig-line {
        border-bottom: 2px solid #333;
        height: 40px;
        margin-bottom: 5px;
    }
    .ts-sig-block label {
        font-size: 11px;
        font-weight: bold;
        color: #333;
    }
    .ts-sig-date {
        margin-top: 8px;
        font-size: 10px;
        color: #555;
    }
    .wo-reference {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #ccc;
        font-size: 10px;
        color: #888;
        text-align: right;
    }
</style>
</head>
<body>
' . $html_content . '
<div class="wo-reference">
    Work Order: ' . htmlspecialchars($order_number) . ' | Generated: ' . date('M d, Y g:i A') . '
</div>
</body>
</html>';

    // Generate PDF with dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $pdfOutput = $dompdf->output();

    if (empty($pdfOutput)) {
        throw new Exception('PDF generation failed - empty output');
    }

    // Save PDF to storage
    $storageRoot = realpath(__DIR__ . '/../storage');
    if (!$storageRoot) {
        $storageFallback = __DIR__ . '/../storage';
        if (!is_dir($storageFallback)) {
            mkdir($storageFallback, 0755, true);
        }
        $storageRoot = realpath($storageFallback);
    }

    $pdfDir = $storageRoot . '/timesheets';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }

    $order_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $order_number);
    $timestamp  = date('Ymd_His');
    $pdf_filename = "TIMESHEET_{$order_safe}_{$timestamp}.pdf";
    $pdf_full_path = $pdfDir . '/' . $pdf_filename;
    $pdf_relative_path = 'timesheets/' . $pdf_filename;

    $bytesWritten = file_put_contents($pdf_full_path, $pdfOutput);
    if ($bytesWritten === false) {
        throw new Exception('Failed to save PDF file. Check directory permissions.');
    }

    // Ensure document_attachments table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `document_attachments` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `document_id` INT NOT NULL,
          `document_type` VARCHAR(50) NOT NULL,
          `file_type` VARCHAR(50) NOT NULL,
          `file_name` VARCHAR(255) NOT NULL,
          `file_path` VARCHAR(500) NOT NULL,
          `uploaded_by` VARCHAR(200) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          INDEX `idx_document` (`document_id`, `document_type`),
          INDEX `idx_file_type` (`file_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Ensure billing_documents table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `billing_documents` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `form_id` INT DEFAULT NULL,
          `order_number` VARCHAR(100) DEFAULT NULL,
          `client_name` VARCHAR(200) DEFAULT NULL,
          `company_name` VARCHAR(200) DEFAULT NULL,
          `document_type` VARCHAR(100) DEFAULT NULL,
          `pdf_path` VARCHAR(500) DEFAULT NULL,
          `service_name` VARCHAR(200) DEFAULT NULL,
          `total_amount` DECIMAL(10,2) DEFAULT NULL,
          `status` VARCHAR(50) DEFAULT 'pending',
          `notes` TEXT DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->beginTransaction();

    $uploaded_by = $_SESSION['full_name'] ?? 'System';

    // Find or create billing document for this form
    $checkStmt = $pdo->prepare("SELECT id FROM billing_documents WHERE form_id = :form_id LIMIT 1");
    $checkStmt->execute([':form_id' => $form_id]);
    $existingBilling = $checkStmt->fetch(PDO::FETCH_ASSOC);

    $billingDocId = null;

    if (!$existingBilling) {
        // Create billing document entry
        $insertStmt = $pdo->prepare("
            INSERT INTO billing_documents
                (form_id, order_number, client_name, company_name, document_type, pdf_path, service_name, total_amount, status, notes)
            VALUES
                (:form_id, :order_number, :client_name, :company_name, :document_type, :pdf_path, :service_name, :total_amount, 'pending', :notes)
        ");

        $insertStmt->execute([
            ':form_id'       => $form_id,
            ':order_number'  => $order_number,
            ':client_name'   => $form['client_name'] ?? '',
            ':company_name'  => $form['company_name'] ?? '',
            ':document_type' => $form['request_type'] ?? 'Timesheet',
            ':pdf_path'      => $pdf_relative_path,
            ':service_name'  => $form['requested_service'] ?? '',
            ':total_amount'  => $form['total_cost'] ?? 0,
            ':notes'         => 'Timesheet attached on ' . date('M d, Y g:i A')
        ]);

        $billingDocId = $pdo->lastInsertId();
    } else {
        $billingDocId = $existingBilling['id'];
    }

    // Attach the timesheet PDF as a document attachment
    if ($billingDocId) {
        $attStmt = $pdo->prepare("
            INSERT INTO document_attachments (document_id, document_type, file_type, file_name, file_path, uploaded_by)
            VALUES (:document_id, :document_type, 'timesheet', :file_name, :file_path, :uploaded_by)
        ");
        $attStmt->execute([
            ':document_id'   => $billingDocId,
            ':document_type' => $form['request_type'] ?? 'Timesheet',
            ':file_name'     => $pdf_filename,
            ':file_path'     => $pdf_relative_path,
            ':uploaded_by'   => $uploaded_by
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Timesheet saved as PDF and linked to Billing.',
        'data'    => [
            'pdf_filename'    => $pdf_filename,
            'pdf_path'        => $pdf_relative_path,
            'order_number'    => $order_number,
            'billing_doc_id'  => $billingDocId,
        ]
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
