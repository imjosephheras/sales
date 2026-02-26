<?php
/**
 * Download a document attachment by ID
 */
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

    $file_path = realpath(__DIR__ . '/../../' . $attachment['file_path']);

    if (!$file_path || !file_exists($file_path)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'File not found on disk']);
        exit;
    }

    // Security: ensure the file is within the uploads directory
    $uploads_dir = realpath(__DIR__ . '/../../uploads/documents');
    if (strpos($file_path, $uploads_dir) !== 0) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    // Serve the file
    $mime = mime_content_type($file_path) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($attachment['file_name']) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');

    readfile($file_path);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
