<?php
/**
 * Upload a document attachment
 * Handles file upload via FileStorageService (local or FTP)
 * and saves metadata to document_attachments table.
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

    $document_id   = $_POST['document_id'] ?? null;
    $document_type = $_POST['document_type'] ?? null;
    $file_type     = $_POST['file_type'] ?? null;
    $uploaded_by   = $_POST['uploaded_by'] ?? ($_SESSION['full_name'] ?? 'Unknown');

    if (!$document_id || !$document_type || !$file_type) {
        echo json_encode(['success' => false, 'error' => 'document_id, document_type, and file_type are required']);
        exit;
    }

    // Validate file_type
    $allowed_file_types = ['timesheet', 'invoice', 'po', 'jwo_pdf', 'other'];
    if (!in_array($file_type, $allowed_file_types)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file_type. Allowed: ' . implode(', ', $allowed_file_types)]);
        exit;
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = 'No file uploaded';
        if (isset($_FILES['file'])) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds server maximum size',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form maximum size',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            ];
            $error_msg = $upload_errors[$_FILES['file']['error']] ?? 'Unknown upload error';
        }
        echo json_encode(['success' => false, 'error' => $error_msg]);
        exit;
    }

    // Upload via FileStorageService (supports local + FTP)
    $storage = new FileStorageService();
    $prefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document_type) . '_' . intval($document_id);
    $result = $storage->uploadFile($_FILES['file'], 'documents', 'documents', $prefix);

    if (!$result['success']) {
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed to upload file']);
        exit;
    }

    $original_name = $_FILES['file']['name'];

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO document_attachments (document_id, document_type, file_type, file_name, file_path, uploaded_by)
        VALUES (:document_id, :document_type, :file_type, :file_name, :file_path, :uploaded_by)
    ");
    $stmt->execute([
        'document_id'   => intval($document_id),
        'document_type' => $document_type,
        'file_type'     => $file_type,
        'file_name'     => $original_name,
        'file_path'     => $result['path'],
        'uploaded_by'   => $uploaded_by,
    ]);

    $insertId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'id'      => $insertId,
        'message' => 'File uploaded successfully',
        'data'    => [
            'id'            => $insertId,
            'file_name'     => $original_name,
            'file_type'     => $file_type,
            'file_path'     => $result['path'],
            'uploaded_by'   => $uploaded_by,
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
