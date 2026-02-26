<?php
/**
 * Secure file serving endpoint
 *
 * Serves files from storage (local or FTP) with authentication and path validation.
 * Usage: /storage/serve.php?file=uploads/form_photos/filename.jpg
 *
 * This prevents direct access to stored files and enforces authentication.
 */
require_once __DIR__ . '/../app/bootstrap.php';

// Require authentication
Middleware::auth();

$requestedFile = $_GET['file'] ?? '';

if (empty($requestedFile)) {
    http_response_code(400);
    exit('Missing file parameter.');
}

$storage = new FileStorageService();

// Only serve allowed content types
$allowedMimes = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];

if ($storage->getDisk() === 'ftp') {
    // Download from FTP to a temp file, then serve it
    $tempPath = $storage->downloadToTemp($requestedFile);

    if (!$tempPath || !file_exists($tempPath)) {
        http_response_code(404);
        exit('File not found.');
    }

    // Detect MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tempPath);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes, true)) {
        @unlink($tempPath);
        http_response_code(403);
        exit('File type not allowed.');
    }

    // Set caching headers for images (1 hour)
    if (str_starts_with($mimeType, 'image/')) {
        header('Cache-Control: private, max-age=3600');
    }

    // Serve the file
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($tempPath));
    header('Content-Disposition: inline; filename="' . basename($requestedFile) . '"');

    readfile($tempPath);

    // Clean up temp file
    @unlink($tempPath);
    exit;
}

// ─── Local storage serving ──────────────────────────────────
$fullPath = $storage->getFullPath($requestedFile);

if (!$fullPath || !file_exists($fullPath)) {
    http_response_code(404);
    exit('File not found.');
}

// Detect MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fullPath);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes, true)) {
    http_response_code(403);
    exit('File type not allowed.');
}

// Set caching headers for images (1 hour)
if (str_starts_with($mimeType, 'image/')) {
    header('Cache-Control: private, max-age=3600');
}

// Serve the file
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');

readfile($fullPath);
exit;
