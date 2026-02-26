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
    'text/csv',
    'text/plain',
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

// Fallback: if getFullPath fails (e.g. realpath returns false for new files),
// try constructing the path directly with security validation
if (!$fullPath || !file_exists($fullPath)) {
    $storageRoot = $storage->getStorageRoot();
    $candidatePath = $storageRoot . '/' . $requestedFile;

    // Resolve the directory (which should exist) and append filename
    $candidateDir = realpath(dirname($candidatePath));
    if ($candidateDir && str_starts_with($candidateDir, $storageRoot)) {
        $directPath = $candidateDir . '/' . basename($candidatePath);
        if (file_exists($directPath) && is_file($directPath)) {
            $fullPath = $directPath;
        }
    }

    // Also try with 'storage/' prefix stripped (backward compatibility)
    if ((!$fullPath || !file_exists($fullPath)) && str_starts_with($requestedFile, 'storage/')) {
        $strippedPath = substr($requestedFile, 8); // Remove 'storage/'
        $candidatePath2 = $storageRoot . '/' . $strippedPath;
        $candidateDir2 = realpath(dirname($candidatePath2));
        if ($candidateDir2 && str_starts_with($candidateDir2, $storageRoot)) {
            $directPath2 = $candidateDir2 . '/' . basename($candidatePath2);
            if (file_exists($directPath2) && is_file($directPath2)) {
                $fullPath = $directPath2;
            }
        }
    }
}

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
