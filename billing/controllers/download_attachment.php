<?php
/**
 * Download a document attachment by ID
 * Serves file via FileStorageService (local or FTP).
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::auth();

require_once __DIR__ . '/../config/db_config.php';

try {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Attachment ID is required']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM document_attachments WHERE id = :id");
    $stmt->execute(['id' => intval($id)]);
    $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attachment) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Attachment not found']);
        exit;
    }

    $storage = new FileStorageService();

    // Get file path (downloads from FTP to temp if needed)
    $filePath = $storage->downloadToTemp($attachment['file_path']);

    if (!$filePath || !file_exists($filePath)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'File not found on server']);
        exit;
    }

    // Detect MIME type
    $mime = mime_content_type($filePath) ?: 'application/octet-stream';

    // Serve the file
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($attachment['file_name']) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');

    readfile($filePath);

    // Clean up temp file if it was downloaded from FTP
    if ($storage->getDisk() === 'ftp') {
        @unlink($filePath);
    }

    exit;

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
