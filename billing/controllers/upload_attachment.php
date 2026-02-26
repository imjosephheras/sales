<?php
/**
 * Upload a document attachment
 * Handles file upload and saves metadata to document_attachments table.
 */
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

    // Validate file size (max 20MB)
    $max_size = 20 * 1024 * 1024;
    if ($_FILES['file']['size'] > $max_size) {
        echo json_encode(['success' => false, 'error' => 'File size exceeds 20MB limit']);
        exit;
    }

    // Validate file extension
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'];
    $original_name = $_FILES['file']['name'];
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', $allowed_extensions)]);
        exit;
    }

    // Build upload path: /uploads/documents/{document_type}_{document_id}/
    $upload_base = realpath(__DIR__ . '/../../uploads/documents');
    if (!$upload_base) {
        // Create directory if it doesn't exist
        $upload_base = __DIR__ . '/../../uploads/documents';
        mkdir($upload_base, 0755, true);
        $upload_base = realpath($upload_base);
    }

    $sub_dir = $upload_base . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $document_type) . '_' . intval($document_id);
    if (!is_dir($sub_dir)) {
        mkdir($sub_dir, 0755, true);
    }

    // Generate unique filename to avoid collisions
    $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
    $file_name = $safe_name . '_' . time() . '.' . $extension;
    $full_path = $sub_dir . '/' . $file_name;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $full_path)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save file']);
        exit;
    }

    // Store relative path from project root
    $relative_path = 'uploads/documents/' . basename($sub_dir) . '/' . $file_name;

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
        'file_path'     => $relative_path,
        'uploaded_by'   => $uploaded_by,
    ]);

    echo json_encode([
        'success' => true,
        'id'      => $pdo->lastInsertId(),
        'message' => 'File uploaded successfully',
        'data'    => [
            'id'            => $pdo->lastInsertId(),
            'file_name'     => $original_name,
            'file_type'     => $file_type,
            'file_path'     => $relative_path,
            'uploaded_by'   => $uploaded_by,
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
