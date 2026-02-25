<?php
/**
 * update_service_status.php
 * Updates the service_status of a form.
 * Writes directly to forms table (single source of truth).
 *
 * When marked as 'completed':
 *   - Sets service_completed_at timestamp
 *   - Generates final PDF
 *   - Sets ready_to_invoice = 1
 *   - Moves to billing "Ready to Invoice" section
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    $request_id = $input['request_id'] ?? null;
    $new_status = $input['status'] ?? null;

    // Validate input
    if (!$request_id || !$new_status) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: request_id and status'
        ]);
        exit;
    }

    // Validate status value
    $valid_statuses = ['pending', 'scheduled', 'confirmed', 'in_progress', 'completed', 'not_completed', 'cancelled'];
    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status. Must be one of: pending, scheduled, confirmed, in_progress, completed, not_completed, cancelled'
        ]);
        exit;
    }

    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Get current form data (single source of truth)
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE form_id = :id");
    $stmt->execute([':id' => $request_id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Form not found'
        ]);
        exit;
    }

    // Build aliased request data for PDF generation compatibility
    $request = [
        'id' => $form['form_id'],
        'Service_Type' => $form['service_type'],
        'Request_Type' => $form['request_type'],
        'Company_Name' => $form['company_name'],
        'client_name' => $form['client_name'],
        'Email' => $form['email'],
        'Number_Phone' => $form['phone'],
        'Seller' => $form['seller'],
        'PriceInput' => $form['total_cost'],
        'Work_Date' => $form['Work_Date'],
        'Document_Date' => $form['Document_Date'],
        'Order_Nomenclature' => $form['Order_Nomenclature'],
    ];

    $pdf_path = null;
    $ready_to_invoice = 0;
    $completed_at = null;

    // If marking as completed, generate PDF and set for billing
    if ($new_status === 'completed') {
        $completed_at = date('Y-m-d H:i:s');
        $ready_to_invoice = 1;

        // Generate final PDF
        $pdf_path = generateFinalPDF($request, $pdo);
    }

    // Update the form directly (single source of truth)
    $updateSql = "
        UPDATE forms SET
            service_status = :status,
            service_completed_at = :completed_at,
            ready_to_invoice = :ready_to_invoice,
            final_pdf_path = :pdf_path,
            updated_at = NOW()
        WHERE form_id = :id
    ";

    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':status' => $new_status,
        ':completed_at' => $completed_at,
        ':ready_to_invoice' => $ready_to_invoice,
        ':pdf_path' => $pdf_path,
        ':id' => $request_id
    ]);

    $pdo->commit();

    // Build response message
    $message = match($new_status) {
        'completed' => 'Service marked as COMPLETED. PDF generated and ready for invoicing.',
        'not_completed' => 'Service marked as NOT COMPLETED. Saved to history.',
        'pending' => 'Service status reset to PENDING.',
        'scheduled' => 'Service marked as SCHEDULED.',
        'confirmed' => 'Service marked as CONFIRMED.',
        'in_progress' => 'Service marked as IN PROGRESS.',
        'cancelled' => 'Service marked as CANCELLED.',
    };

    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'request_id' => $request_id,
            'new_status' => $new_status,
            'ready_to_invoice' => $ready_to_invoice,
            'pdf_path' => $pdf_path,
            'completed_at' => $completed_at
        ]
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error updating service status: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating the status. Please try again later.'
    ]);
}

/**
 * Generate final PDF for completed service
 */
function generateFinalPDF($request, $pdo) {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);

    // Get logo (dynamic based on Service_Type)
    $dept = strtolower(trim($request['Service_Type'] ?? ''));
    if (strpos($dept, 'hospitality') !== false) {
        $logo_path = dirname(__DIR__, 2) . '/Images/phospitality.png';
    } else {
        $logo_path = dirname(__DIR__, 2) . '/Images/pfacility.png';
    }
    $image_src = '';
    if (file_exists($logo_path)) {
        $image_data = base64_encode(file_get_contents($logo_path));
        $image_src = 'data:image/png;base64,' . $image_data;
    }

    // Build HTML content
    $html = buildFinalPDFContent($request, $image_src);

    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save PDF to file
    $pdf_dir = dirname(__DIR__) . '/pdfs/';
    if (!is_dir($pdf_dir)) {
        mkdir($pdf_dir, 0777, true);
    }

    $filename = 'final_' . $request['id'] . '_' . date('Ymd_His') . '.pdf';
    $pdf_path = $pdf_dir . $filename;

    file_put_contents($pdf_path, $dompdf->output());

    return $pdf_path;
}

/**
 * Build HTML content for final PDF
 */
function buildFinalPDFContent($request, $image_src) {
    $company = htmlspecialchars($request['Company_Name'] ?? '');
    $client = htmlspecialchars($request['client_name'] ?? '');
    $email = htmlspecialchars($request['Email'] ?? '');
    $phone = htmlspecialchars($request['Number_Phone'] ?? '');
    $service_type = htmlspecialchars($request['Service_Type'] ?? '');
    $request_type = htmlspecialchars($request['Request_Type'] ?? '');
    $seller = htmlspecialchars($request['Seller'] ?? '');
    $price = htmlspecialchars($request['PriceInput'] ?? '');
    $work_date = htmlspecialchars($request['Work_Date'] ?? '');
    $document_date = htmlspecialchars($request['Document_Date'] ?? '');
    $nomenclature = htmlspecialchars($request['Order_Nomenclature'] ?? '');
    $completed_date = date('m/d/Y H:i:s');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #fff;
    color: #333;
    margin: 20px;
  }
  .header {
    text-align: center;
    margin-bottom: 30px;
  }
  .header img {
    max-width: 200px;
    margin-bottom: 10px;
  }
  h1 {
    color: #001f54;
    margin: 10px 0;
    font-size: 24px;
  }
  .status-badge {
    display: inline-block;
    background: #28a745;
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
    margin-top: 10px;
  }
  .section {
    margin-top: 25px;
    border-left: 4px solid #001f54;
    padding-left: 15px;
  }
  .section h2 {
    color: #001f54;
    font-size: 16px;
    margin-bottom: 10px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
  }
  .field {
    margin-bottom: 8px;
    display: flex;
  }
  .label {
    font-weight: bold;
    min-width: 180px;
    color: #555;
  }
  .value {
    color: #333;
  }
  .footer {
    margin-top: 40px;
    text-align: center;
    font-size: 12px;
    color: #777;
    border-top: 1px solid #ddd;
    padding-top: 20px;
  }
  .nomenclature {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    color: #001f54;
    margin: 20px 0;
  }
</style>
</head>
<body>

<div class="header">
  <img src="{$image_src}" alt="Logo">
  <h1>SERVICE COMPLETION REPORT</h1>
  <div class="status-badge">SERVICE COMPLETED</div>
</div>

<div class="nomenclature">
  Order: {$nomenclature}
</div>

<div class="section">
  <h2>Service Information</h2>
  <div class="field"><span class="label">Service Type:</span><span class="value">{$service_type}</span></div>
  <div class="field"><span class="label">Request Type:</span><span class="value">{$request_type}</span></div>
  <div class="field"><span class="label">Seller:</span><span class="value">{$seller}</span></div>
  <div class="field"><span class="label">Price:</span><span class="value">{$price}</span></div>
</div>

<div class="section">
  <h2>Client Information</h2>
  <div class="field"><span class="label">Company:</span><span class="value">{$company}</span></div>
  <div class="field"><span class="label">Client Name:</span><span class="value">{$client}</span></div>
  <div class="field"><span class="label">Email:</span><span class="value">{$email}</span></div>
  <div class="field"><span class="label">Phone:</span><span class="value">{$phone}</span></div>
</div>

<div class="section">
  <h2>Dates</h2>
  <div class="field"><span class="label">Document Date:</span><span class="value">{$document_date}</span></div>
  <div class="field"><span class="label">Work Date:</span><span class="value">{$work_date}</span></div>
  <div class="field"><span class="label">Completed Date:</span><span class="value">{$completed_date}</span></div>
</div>

<div class="footer">
  <p>This document certifies that the service has been completed.</p>
  <p>Generated on {$completed_date} - Prime Facility Services Group</p>
</div>

</body>
</html>
HTML;
}
?>
