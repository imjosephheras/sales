<?php
/**
 * GET BILLING HISTORY CONTROLLER
 * Returns completed billing documents.
 * Reads from billing_documents + forms (single source of truth).
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';

try {
    $sql = "SELECT
                bd.*,
                f.company_name,
                f.client_name,
                f.request_type,
                f.requested_service,
                f.grand_total,
                f.docnum AS form_docnum
            FROM billing_documents bd
            LEFT JOIN forms f ON bd.form_id = f.form_id
            WHERE bd.status = 'completed'
            ORDER BY bd.updated_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($documents as &$doc) {
        if ($doc['created_at']) {
            $doc['created_at_formatted'] = date('M d, Y g:i A', strtotime($doc['created_at']));
        }
        if ($doc['updated_at']) {
            $doc['updated_at_formatted'] = date('M d, Y g:i A', strtotime($doc['updated_at']));
        }
        $doc['company_name'] = $doc['company_name'] ?? 'N/A';
        $doc['client_name'] = $doc['client_name'] ?? 'N/A';
    }

    echo json_encode([
        'success' => true,
        'data' => $documents,
        'count' => count($documents)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
