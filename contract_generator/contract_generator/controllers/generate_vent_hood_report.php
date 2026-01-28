<?php
/**
 * GENERATE VENT HOOD REPORT PDF CONTROLLER
 * Generates PDF for Vent Hood Service Reports
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// Load Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';

// Load database configuration
require_once __DIR__ . '/../config/db_config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Verify that an ID was received
    $request_id = $_GET['id'] ?? $_POST['id'] ?? null;

    if (!$request_id) {
        throw new Exception('Request ID is required');
    }

    // ========================================
    // GET DATA FROM DATABASE
    // ========================================

    $sql = "SELECT * FROM requests WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $request_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('Request not found');
    }

    // ========================================
    // USE VENT HOOD REPORT TEMPLATE
    // ========================================

    $template_file = __DIR__ . "/../templates/vent_hood_report.php";

    if (!file_exists($template_file)) {
        throw new Exception("Vent Hood Report template not found");
    }

    // ========================================
    // RENDER TEMPLATE
    // ========================================

    // Capture template output
    ob_start();
    include $template_file;
    $html = ob_get_clean();

    // ========================================
    // GENERATE PDF WITH DOMPDF
    // ========================================

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    // ========================================
    // DOWNLOAD PDF
    // ========================================

    // Generate filename
    $doc_number = $data['docnum'] ?? 'DRAFT';
    $company_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['Company_Name'] ?? 'Document');
    $pdf_filename = "VENT_HOOD_REPORT_{$doc_number}_{$company_name}.pdf";

    // Send headers for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdf_filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    echo $dompdf->output();

} catch (Exception $e) {
    http_response_code(400);

    // If it's an AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        // If it's a normal request, show HTML error
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 40px;
                    background: #f5f5f5;
                }
                .error-box {
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    border-left: 4px solid #d32f2f;
                    max-width: 600px;
                    margin: 0 auto;
                }
                h1 {
                    color: #d32f2f;
                    margin-top: 0;
                }
                .error-message {
                    color: #666;
                    line-height: 1.6;
                }
                .back-link {
                    display: inline-block;
                    margin-top: 20px;
                    color: #1976d2;
                    text-decoration: none;
                }
                .back-link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>Error Generating Vent Hood Report</h1>
                <p class='error-message'>" . htmlspecialchars($e->getMessage()) . "</p>
                <a href='javascript:history.back()' class='back-link'>Go Back</a>
            </div>
        </body>
        </html>";
    }
    exit;
}
?>
