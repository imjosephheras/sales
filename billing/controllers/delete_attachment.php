<?php
/**
 * Delete a document attachment
 * Removes file via FileStorageService (local or FTP) and deletes DB record.
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::auth();

require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Attachment ID is required']);
        exit;
    }

    // Get attachment info before deleting
    $stmt = $pdo->prepare("SELECT * FROM document_attachments WHERE id = :id");
    $stmt->execute(['id' => intval($id)]);
    $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attachment) {
        echo json_encode(['success' => false, 'error' => 'Attachment not found']);
        exit;
    }

    // Delete file from storage (local or FTP) via FileStorageService
    if (!empty($attachment['file_path'])) {
        $storage = new FileStorageService();
        $storage->deleteFile($attachment['file_path']);
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM document_attachments WHERE id = :id");
    $stmt->execute(['id' => intval($id)]);

    echo json_encode([
        'success' => true,
        'message' => 'Attachment deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
