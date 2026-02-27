<?php
/**
 * MARK COMPLETED CONTROLLER
 * When a contract is marked as Completed in the Contract Generator:
 * 1. Sets status = 'completed'
 * 2. Generates final immutable PDF and saves it to disk
 * 3. Automatically creates a billing_documents record with status='pending'
 *
 * Reads from forms + contract_items (single source of truth).
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/db_config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['request_id'] ?? null;

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

    // Must be in 'ready' status to mark completed
    if ($form['status'] !== 'ready') {
        throw new Exception('Only forms with status "ready" can be marked as completed. Current status: ' . $form['status']);
    }

    $docnum = $form['docnum'] ?? $form['Order_Nomenclature'];
    if (empty($docnum)) {
        throw new Exception('DOCNUM is required to mark as completed. Please mark as Ready first.');
    }

    // ========================================
    // 1. GENERATE FINAL IMMUTABLE PDF
    // ========================================

    // Get contract items (replaces janitorial_services_costs, kitchen_cleaning_costs, hood_vent_costs)
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

    // Build $data array compatible with templates (maps forms columns to expected field names)
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
        'docnum' => $docnum,
        'Document_Date' => $form['Document_Date'],
        'Work_Date' => $form['Work_Date'],
        'Order_Nomenclature' => $form['Order_Nomenclature'],
        'order_number' => $form['order_number'],
        'total_cost' => $form['total_cost'],
    ];

    // Sales mode: 'service' (default) or 'product' — affects only visible headers
    $salesMode = ($input['sales_mode'] ?? 'service') === 'product' ? 'product' : 'service';

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

    // Generate PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $pdfOutput = $dompdf->output();

    // Validate PDF output
    if (empty($pdfOutput)) {
        throw new Exception('PDF generation failed - empty output from DOMPDF');
    }

    // Save PDF to disk (immutable final copy)
    // Resolve storage root to canonical path for consistency with FileStorageService
    // Path: contract_generator/controllers/../../storage → sales/storage
    $storageRoot = realpath(__DIR__ . '/../../storage');
    if (!$storageRoot) {
        $storageFallback = __DIR__ . '/../../storage';
        if (!is_dir($storageFallback)) {
            mkdir($storageFallback, 0755, true);
        }
        $storageRoot = realpath($storageFallback);
        if (!$storageRoot) {
            throw new Exception('Could not resolve storage directory');
        }
    }

    $pdfDir = $storageRoot . '/final_pdfs';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }

    $company_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['Company_Name'] ?? 'Document');
    $pdf_filename = strtoupper($request_type) . "_{$docnum}_{$company_safe}_FINAL.pdf";
    $pdf_full_path = $pdfDir . '/' . $pdf_filename;
    // Store path relative to storage root (consistent with FileStorageService conventions)
    $pdf_relative_path = 'final_pdfs/' . $pdf_filename;

    $bytesWritten = file_put_contents($pdf_full_path, $pdfOutput);
    if ($bytesWritten === false) {
        throw new Exception('Failed to save PDF file to disk. Check directory permissions on storage/final_pdfs/');
    }

    // Verify file was actually written
    if (!file_exists($pdf_full_path)) {
        throw new Exception('PDF file verification failed - file not found after writing');
    }

    // ========================================
    // 2. UPDATE FORM STATUS TO COMPLETED
    // ========================================

    // Ensure document_attachments table exists BEFORE starting transaction.
    // DDL statements (CREATE TABLE) cause an implicit COMMIT in MySQL,
    // which would break any active transaction.
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

    $pdo->beginTransaction();

    session_start();
    $completed_by = $_SESSION['user_id'] ?? null;
    $completed_by_name = $_SESSION['full_name'] ?? 'System';

    $stmt = $pdo->prepare("
        UPDATE forms
        SET
            status = 'completed',
            final_pdf_path = :pdf_path,
            completed_at = NOW(),
            updated_at = NOW()
        WHERE form_id = :id
    ");

    $stmt->execute([
        ':pdf_path' => $pdf_relative_path,
        ':id' => $id
    ]);

    // ========================================
    // 3. AUTO-SEND TO BILLING AS PENDING
    // ========================================

    // Check if billing document already exists for this form
    $checkStmt = $pdo->prepare("SELECT id FROM billing_documents WHERE form_id = :form_id LIMIT 1");
    $checkStmt->execute([':form_id' => $id]);
    $existingBilling = $checkStmt->fetch();

    $billingDocId = null;

    if (!$existingBilling) {
        $totalAmount = $form['total_cost'] ?? '';

        $insertStmt = $pdo->prepare("
            INSERT INTO billing_documents
                (form_id, order_number, client_name, company_name, document_type, pdf_path, service_name, total_amount, status, notes)
            VALUES
                (:form_id, :order_number, :client_name, :company_name, :document_type, :pdf_path, :service_name, :total_amount, 'pending', :notes)
        ");

        $insertStmt->execute([
            ':form_id' => $id,
            ':order_number' => $docnum,
            ':client_name' => $form['client_name'] ?? '',
            ':company_name' => $form['company_name'] ?? '',
            ':document_type' => $form['request_type'] ?? 'Contract',
            ':pdf_path' => $pdf_relative_path,
            ':service_name' => $form['requested_service'] ?? '',
            ':total_amount' => $totalAmount,
            ':notes' => 'Auto-generated from Contract Generator on ' . date('M d, Y g:i A')
        ]);

        $billingDocId = $pdo->lastInsertId();
    } else {
        $billingDocId = $existingBilling['id'];
        // Update pdf_path on existing billing record in case it was missing or outdated
        $updateStmt = $pdo->prepare("UPDATE billing_documents SET pdf_path = :pdf_path WHERE id = :id");
        $updateStmt->execute([':pdf_path' => $pdf_relative_path, ':id' => $billingDocId]);
    }

    // ========================================
    // 4. AUTO-ATTACH FINAL PDF AS DOCUMENT ATTACHMENT
    // ========================================

    if ($billingDocId) {
        $documentType = $form['request_type'] ?? 'Contract';

        // Check if this PDF was already attached
        $checkAtt = $pdo->prepare("
            SELECT id FROM document_attachments
            WHERE document_id = :doc_id AND document_type = :doc_type AND file_type = 'jwo_pdf' AND file_name = :file_name
            LIMIT 1
        ");
        $checkAtt->execute([
            ':doc_id' => $billingDocId,
            ':doc_type' => $documentType,
            ':file_name' => $pdf_filename
        ]);

        if (!$checkAtt->fetch()) {
            $attStmt = $pdo->prepare("
                INSERT INTO document_attachments (document_id, document_type, file_type, file_name, file_path, uploaded_by)
                VALUES (:document_id, :document_type, 'jwo_pdf', :file_name, :file_path, :uploaded_by)
            ");
            $attStmt->execute([
                ':document_id' => $billingDocId,
                ':document_type' => $documentType,
                ':file_name' => $pdf_filename,
                ':file_path' => $pdf_relative_path,
                ':uploaded_by' => $completed_by_name
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Contract marked as completed. Final PDF generated and sent to Accounting.',
        'docnum' => $docnum,
        'request_id' => $id,
        'pdf_path' => $pdf_relative_path
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
