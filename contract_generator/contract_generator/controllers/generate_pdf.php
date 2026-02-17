<?php
/**
 * GENERATE PDF CONTROLLER
 * Genera PDFs basados en templates según el tipo de request
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// Cargar autoload de Composer
require_once __DIR__ . '/../../../vendor/autoload.php';

// Cargar configuración de base de datos
require_once __DIR__ . '/../config/db_config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Verificar que se recibió un ID
    $request_id = $_GET['id'] ?? $_POST['id'] ?? null;

    if (!$request_id) {
        throw new Exception('Request ID is required');
    }

    // ========================================
    // OBTENER DATOS DE LA BASE DE DATOS
    // ========================================

    $sql = "SELECT * FROM requests WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $request_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('Request not found');
    }

    // ========================================
    // QUERY SERVICE DETAIL TABLES FROM form DB
    // ========================================

    $formId = $data['form_id'] ?? null;

    // Try to find form_id by docnum if not directly set
    if (!$formId && !empty($data['docnum'])) {
        $stmtForm = $pdo->prepare("SELECT form_id FROM forms WHERE Order_Nomenclature = ? LIMIT 1");
        $stmtForm->execute([$data['docnum']]);
        $formRow = $stmtForm->fetch(PDO::FETCH_ASSOC);
        if ($formRow) {
            $formId = $formRow['form_id'];
        }
    }

    // Fetch Work_Date and Document_Date from forms table
    if ($formId) {
        $stmtFormDates = $pdo->prepare("SELECT Work_Date, Document_Date FROM forms WHERE form_id = ? LIMIT 1");
        $stmtFormDates->execute([$formId]);
        $formDates = $stmtFormDates->fetch(PDO::FETCH_ASSOC);
        if ($formDates) {
            $data['Work_Date'] = $formDates['Work_Date'];
            $data['Document_Date'] = $formDates['Document_Date'];
        }
    }

    $janitorialServices = [];
    $kitchenServices = [];
    $hoodVentServices = [];
    $scopeOfWorkTasks = [];
    $scopeSections = [];

    if ($formId) {
        // Janitorial services from detail table
        $stmtJ = $pdo->prepare("SELECT * FROM janitorial_services_costs WHERE form_id = ? ORDER BY service_number");
        $stmtJ->execute([$formId]);
        $janitorialServices = $stmtJ->fetchAll(PDO::FETCH_ASSOC);

        // Kitchen cleaning services from detail table
        $stmtK = $pdo->prepare("SELECT * FROM kitchen_cleaning_costs WHERE form_id = ? ORDER BY service_number");
        $stmtK->execute([$formId]);
        $kitchenServices = $stmtK->fetchAll(PDO::FETCH_ASSOC);

        // Hood vent services from detail table
        $stmtH = $pdo->prepare("SELECT * FROM hood_vent_costs WHERE form_id = ? ORDER BY service_number");
        $stmtH->execute([$formId]);
        $hoodVentServices = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        // Scope of work tasks from detail table
        $stmtS = $pdo->prepare("SELECT task_name FROM scope_of_work WHERE form_id = ?");
        $stmtS->execute([$formId]);
        $scopeOfWorkTasks = $stmtS->fetchAll(PDO::FETCH_COLUMN);

        // Scope sections (dynamic blocks) from detail table
        $stmtSS = $pdo->prepare("SELECT title, scope_content FROM scope_sections WHERE form_id = ? ORDER BY section_order ASC");
        $stmtSS->execute([$formId]);
        $scopeSections = $stmtSS->fetchAll(PDO::FETCH_ASSOC);
    }

    // Decode JSON fields in data for fallback
    $jsonFields = [
        'type18', 'write18', 'time18', 'freq18', 'desc18', 'subtotal18',
        'type19', 'time19', 'freq19', 'desc19', 'subtotal19',
        'base_staff', 'increase_staff', 'bill_staff'
    ];
    foreach ($jsonFields as $field) {
        if (!empty($data[$field])) {
            $decoded = json_decode($data[$field], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data[$field] = $decoded;
            }
        }
    }

    // Decode Scope_Of_Work separately to handle nested structure
    if (!empty($data['Scope_Of_Work']) && is_string($data['Scope_Of_Work'])) {
        $decoded = json_decode($data['Scope_Of_Work'], true);
        if (is_array($decoded)) {
            if (isset($decoded['items']) && is_array($decoded['items'])) {
                $data['Scope_Of_Work'] = array_filter($decoded['items'], 'is_string');
                if (empty($scopeSections) && !empty($decoded['sections']) && is_array($decoded['sections'])) {
                    $scopeSections = $decoded['sections'];
                }
            } else {
                $data['Scope_Of_Work'] = array_filter($decoded, 'is_string');
            }
        }
    }

    // ========================================
    // DETERMINAR QUÉ TEMPLATE USAR
    // ========================================

    $request_type = strtolower($data['Request_Type'] ?? 'quote');
    $template_file = __DIR__ . "/../templates/{$request_type}.php";

    if (!file_exists($template_file)) {
        throw new Exception("Template not found for type: {$request_type}");
    }

    // ========================================
    // RENDERIZAR TEMPLATE
    // ========================================

    // Capturar output del template
    ob_start();
    include $template_file;
    $html = ob_get_clean();

    // ========================================
    // GENERAR PDF CON DOMPDF
    // ========================================

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // ========================================
    // DESCARGAR PDF
    // ========================================

    // Generar nombre de archivo
    $doc_number = $data['docnum'] ?? 'DRAFT';
    $company_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['Company_Name'] ?? 'Document');
    $pdf_filename = strtoupper($request_type) . "_{$doc_number}_{$company_name}.pdf";

    // Enviar headers para descarga
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdf_filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    echo $dompdf->output();

} catch (Exception $e) {
    http_response_code(400);

    // Si es una solicitud AJAX, devolver JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        // Si es una solicitud normal, mostrar error HTML
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
                <h1>⚠️ Error Generating PDF</h1>
                <p class='error-message'>" . htmlspecialchars($e->getMessage()) . "</p>
                <a href='javascript:history.back()' class='back-link'>← Go Back</a>
            </div>
        </body>
        </html>";
    }
    exit;
}
?>
