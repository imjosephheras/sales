<?php
/**
 * MARK COMPLETED CONTROLLER
 * When a contract is marked as Completed in the Contract Generator:
 * 1. Sets status = 'completed'
 * 2. Generates final immutable PDF and saves it to disk
 * 3. Automatically creates a billing_documents record with status='pending'
 *
 * This is the ONLY trigger for the accounting flow.
 * No Admin Panel action is needed.
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../vendor/autoload.php';
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
        throw new Exception('Request ID is required');
    }

    // Get request data
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request not found');
    }

    // Must be in 'ready' status to mark completed
    if ($request['status'] !== 'ready') {
        throw new Exception('Only requests with status "ready" can be marked as completed. Current status: ' . $request['status']);
    }

    if (empty($request['docnum'])) {
        throw new Exception('DOCNUM is required to mark as completed. Please mark as Ready first.');
    }

    // ========================================
    // 1. GENERATE FINAL IMMUTABLE PDF
    // ========================================

    // Get form_id for service detail tables
    $formId = $request['form_id'] ?? null;
    if (!$formId && !empty($request['docnum'])) {
        $stmtForm = $pdo->prepare("SELECT form_id FROM forms WHERE Order_Nomenclature = ? LIMIT 1");
        $stmtForm->execute([$request['docnum']]);
        $formRow = $stmtForm->fetch(PDO::FETCH_ASSOC);
        if ($formRow) {
            $formId = $formRow['form_id'];
        }
    }

    // Query service detail tables
    $janitorialServices = [];
    $kitchenServices = [];
    $hoodVentServices = [];
    $scopeOfWorkTasks = [];

    if ($formId) {
        $stmtJ = $pdo->prepare("SELECT * FROM janitorial_services_costs WHERE form_id = ? ORDER BY service_number");
        $stmtJ->execute([$formId]);
        $janitorialServices = $stmtJ->fetchAll(PDO::FETCH_ASSOC);

        $stmtK = $pdo->prepare("SELECT * FROM kitchen_cleaning_costs WHERE form_id = ? ORDER BY service_number");
        $stmtK->execute([$formId]);
        $kitchenServices = $stmtK->fetchAll(PDO::FETCH_ASSOC);

        $stmtH = $pdo->prepare("SELECT * FROM hood_vent_costs WHERE form_id = ? ORDER BY service_number");
        $stmtH->execute([$formId]);
        $hoodVentServices = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        $stmtS = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
        $stmtS->execute([$formId]);
        $scopeOfWorkTasks = $stmtS->fetchAll(PDO::FETCH_COLUMN);
    }

    // Decode JSON fields
    $data = $request;
    $jsonFields = [
        'type18', 'write18', 'time18', 'freq18', 'desc18', 'subtotal18',
        'type19', 'time19', 'freq19', 'desc19', 'subtotal19',
        'base_staff', 'increase_staff', 'bill_staff', 'Scope_Of_Work'
    ];
    foreach ($jsonFields as $field) {
        if (!empty($data[$field])) {
            $decoded = json_decode($data[$field], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data[$field] = $decoded;
            }
        }
    }

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

    // Save PDF to disk (immutable final copy)
    $pdfDir = __DIR__ . '/../../../storage/final_pdfs';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }

    $doc_number = $data['docnum'];
    $company_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['Company_Name'] ?? 'Document');
    $pdf_filename = strtoupper($request_type) . "_{$doc_number}_{$company_safe}_FINAL.pdf";
    $pdf_full_path = $pdfDir . '/' . $pdf_filename;
    $pdf_relative_path = 'storage/final_pdfs/' . $pdf_filename;

    file_put_contents($pdf_full_path, $pdfOutput);

    // ========================================
    // 2. UPDATE REQUEST STATUS TO COMPLETED
    // ========================================

    $pdo->beginTransaction();

    // Get current user info from session
    session_start();
    $completed_by = $_SESSION['user_id'] ?? null;
    $completed_by_name = $_SESSION['full_name'] ?? 'System';

    $stmt = $pdo->prepare("
        UPDATE requests
        SET
            status = 'completed',
            final_pdf_path = :pdf_path,
            completed_at = NOW(),
            updated_at = NOW()
        WHERE id = :id
    ");

    $stmt->execute([
        ':pdf_path' => $pdf_relative_path,
        ':id' => $id
    ]);

    // ========================================
    // 3. AUTO-SEND TO BILLING AS PENDING
    // ========================================

    // Ensure billing_documents table has required columns
    try {
        $pdo->query("SELECT completed_by FROM billing_documents LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE billing_documents ADD COLUMN completed_by INT DEFAULT NULL");
        $pdo->exec("ALTER TABLE billing_documents ADD COLUMN completed_by_name VARCHAR(200) DEFAULT NULL");
        $pdo->exec("ALTER TABLE billing_documents ADD COLUMN service_name VARCHAR(200) DEFAULT NULL");
        $pdo->exec("ALTER TABLE billing_documents ADD COLUMN total_amount VARCHAR(100) DEFAULT NULL");
    }

    // Check if billing document already exists for this request
    $checkStmt = $pdo->prepare("SELECT id FROM billing_documents WHERE request_id = :request_id LIMIT 1");
    $checkStmt->execute([':request_id' => $id]);
    $existingBilling = $checkStmt->fetch();

    if (!$existingBilling) {
        // Calculate total amount
        $totalAmount = $data['PriceInput'] ?? '';
        if (empty($totalAmount)) {
            $totalAmount = $data['grand18'] ?? '';
            if (!empty($data['grand19'])) {
                if (!empty($totalAmount)) {
                    $totalAmount .= ' + ' . $data['grand19'];
                } else {
                    $totalAmount = $data['grand19'];
                }
            }
        }

        $insertStmt = $pdo->prepare("
            INSERT INTO billing_documents
                (request_id, order_number, client_name, company_name, document_type, pdf_path, service_name, total_amount, status, notes)
            VALUES
                (:request_id, :order_number, :client_name, :company_name, :document_type, :pdf_path, :service_name, :total_amount, 'pending', :notes)
        ");

        $insertStmt->execute([
            ':request_id' => $id,
            ':order_number' => $data['docnum'],
            ':client_name' => $data['client_name'] ?? $data['Client_Name'] ?? '',
            ':company_name' => $data['Company_Name'] ?? '',
            ':document_type' => $data['Request_Type'] ?? 'Contract',
            ':pdf_path' => $pdf_relative_path,
            ':service_name' => $data['Requested_Service'] ?? '',
            ':total_amount' => $totalAmount,
            ':notes' => 'Auto-generated from Contract Generator on ' . date('M d, Y g:i A')
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Contract marked as completed. Final PDF generated and sent to Accounting.',
        'docnum' => $data['docnum'],
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
