<?php
/**
 * Mark a billing document as completed
 */
require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Document ID is required']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE billing_documents SET status = 'completed', completed_at = NOW() WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Document marked as completed']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Document not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
