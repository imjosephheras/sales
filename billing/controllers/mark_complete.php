<?php
/**
 * Mark a billing document as completed or revert to pending.
 * Supports actions: 'complete' and 'pending'
 * Records who performed the action and when.
 */
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    $action = $input['action'] ?? 'complete';

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Document ID is required']);
        exit;
    }

    // Get current user info
    $userId = $_SESSION['user_id'] ?? null;
    $userName = $_SESSION['full_name'] ?? 'Unknown';

    if ($action === 'complete') {
        $stmt = $pdo->prepare("
            UPDATE billing_documents
            SET status = 'completed',
                completed_at = NOW(),
                completed_by = :user_id,
                completed_by_name = :user_name
            WHERE id = :id
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':user_name' => $userName,
            ':id' => $id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Contract marked as completed by ' . $userName]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Document not found']);
        }
    } elseif ($action === 'pending') {
        $stmt = $pdo->prepare("
            UPDATE billing_documents
            SET status = 'pending',
                completed_at = NULL,
                completed_by = NULL,
                completed_by_name = NULL
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Contract reverted to pending']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Document not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
