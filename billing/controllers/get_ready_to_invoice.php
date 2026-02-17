<?php
/**
 * GET READY TO INVOICE CONTROLLER
 * Returns forms that are completed and ready to be invoiced.
 * Reads from forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';

try {
    $sql = "SELECT
                f.form_id AS id,
                f.form_id,
                f.request_type AS Request_Type,
                f.company_name AS Company_Name,
                f.client_name,
                f.requested_service AS Requested_Service,
                f.grand_total,
                f.docnum,
                f.status,
                f.service_status,
                f.final_pdf_path,
                f.completed_at,
                f.created_at
            FROM forms f
            WHERE f.status = 'completed'
              AND f.ready_to_invoice = 1
            ORDER BY f.completed_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($forms as &$form) {
        $checkStmt = $pdo->prepare("SELECT id, status FROM billing_documents WHERE form_id = ? LIMIT 1");
        $checkStmt->execute([$form['form_id']]);
        $billingDoc = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $form['has_billing_document'] = $billingDoc ? true : false;
        $form['billing_status'] = $billingDoc ? $billingDoc['status'] : null;

        if ($form['completed_at']) {
            $form['completed_at_formatted'] = date('M d, Y g:i A', strtotime($form['completed_at']));
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $forms,
        'count' => count($forms)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
