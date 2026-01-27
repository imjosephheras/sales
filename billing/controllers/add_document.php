<?php
/**
 * Add a new billing document (called from contract generator when a contract is ready)
 */
require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $order_number = $input['order_number'] ?? null;
    $client_name = $input['client_name'] ?? null;
    $company_name = $input['company_name'] ?? null;
    $document_type = $input['document_type'] ?? null;
    $pdf_path = $input['pdf_path'] ?? null;
    $request_id = $input['request_id'] ?? null;
    $notes = $input['notes'] ?? null;

    if (!$order_number) {
        echo json_encode(['success' => false, 'error' => 'Order number is required']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO billing_documents (request_id, order_number, client_name, company_name, document_type, pdf_path, notes, status)
        VALUES (:request_id, :order_number, :client_name, :company_name, :document_type, :pdf_path, :notes, 'pending')
    ");

    $stmt->execute([
        'request_id' => $request_id,
        'order_number' => $order_number,
        'client_name' => $client_name,
        'company_name' => $company_name,
        'document_type' => $document_type,
        'pdf_path' => $pdf_path,
        'notes' => $notes
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Document added to billing']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
