<?php
/**
 * Add a new billing document
 * Uses form_id to reference the forms table (single source of truth).
 */
require_once __DIR__ . '/../config/init.php';
$pdo = Database::getConnection();
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $order_number = $input['order_number'] ?? null;
    $client_name = $input['client_name'] ?? null;
    $company_name = $input['company_name'] ?? null;
    $document_type = $input['document_type'] ?? null;
    $pdf_path = $input['pdf_path'] ?? null;
    $form_id = $input['form_id'] ?? $input['request_id'] ?? null;
    $notes = $input['notes'] ?? null;

    if (!$order_number) {
        echo json_encode(['success' => false, 'error' => 'Order number is required']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO billing_documents (form_id, order_number, client_name, company_name, document_type, pdf_path, notes, status)
        VALUES (:form_id, :order_number, :client_name, :company_name, :document_type, :pdf_path, :notes, 'pending')
    ");

    $stmt->execute([
        'form_id' => $form_id,
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
