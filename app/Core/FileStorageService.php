<?php
/**
 * FileStorageService - Centralized file upload and storage management
 *
 * Handles file validation, sanitization, storage, and retrieval
 * for all modules (form photos, work reports, profile photos, documents).
 */
class FileStorageService
{
    /** Base storage directory */
    private string $storageRoot;

    /** Allowed MIME types grouped by category */
    private const ALLOWED_TYPES = [
        'images' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
        'documents' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ];

    /** Max file sizes in bytes per category */
    private const MAX_SIZES = [
        'images'    => 10 * 1024 * 1024,  // 10 MB
        'documents' => 20 * 1024 * 1024,  // 20 MB
    ];

    /** Subdirectory mapping for each storage context */
    private const STORAGE_PATHS = [
        'form_photos'        => 'uploads/form_photos',
        'work_report_photos' => 'uploads/work_report_photos',
        'profile_photos'     => 'uploads/profile_photos',
        'documents'          => 'uploads/documents',
        'final_pdfs'         => 'final_pdfs',
    ];

    public function __construct(?string $storageRoot = null)
    {
        $this->storageRoot = $storageRoot ?? realpath(__DIR__ . '/../../storage');

        if (!$this->storageRoot || !is_dir($this->storageRoot)) {
            $fallback = __DIR__ . '/../../storage';
            if (!is_dir($fallback)) {
                mkdir($fallback, 0755, true);
            }
            $this->storageRoot = realpath($fallback);
        }
    }

    /**
     * Upload a single file from $_FILES input
     *
     * @param array  $file     Single file from $_FILES (e.g. $_FILES['photo'])
     * @param string $context  Storage context: 'form_photos', 'profile_photos', etc.
     * @param string $category Validation category: 'images' or 'documents'
     * @param string $prefix   Optional filename prefix (e.g. 'user_5')
     * @return array{success: bool, filename?: string, path?: string, size?: int, type?: string, error?: string}
     */
    public function uploadFile(array $file, string $context, string $category = 'images', string $prefix = ''): array
    {
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->getUploadErrorMessage($file['error'] ?? -1)];
        }

        // Validate MIME type using finfo (real type detection, not trusting extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!$this->isAllowedType($mimeType, $category)) {
            $allowed = implode(', ', self::ALLOWED_TYPES[$category] ?? []);
            return ['success' => false, 'error' => "Tipo de archivo no permitido ({$mimeType}). Permitidos: {$allowed}"];
        }

        // Validate file size
        $maxSize = self::MAX_SIZES[$category] ?? self::MAX_SIZES['images'];
        if ($file['size'] > $maxSize) {
            $maxMB = round($maxSize / 1024 / 1024, 1);
            return ['success' => false, 'error' => "El archivo excede el tamaño máximo permitido ({$maxMB} MB)."];
        }

        // Verify this is a real uploaded file (security check)
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'El archivo no es una carga válida.'];
        }

        // Get destination directory
        $destDir = $this->getStoragePath($context);
        if (!$destDir) {
            return ['success' => false, 'error' => "Contexto de almacenamiento no válido: {$context}"];
        }

        // Ensure directory exists
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Generate safe filename
        $extension = $this->getSafeExtension($file['name'], $mimeType);
        $safeName = $this->sanitizeFilename($file['name']);
        $timestamp = time() . '_' . bin2hex(random_bytes(4));
        $newFilename = ($prefix ? $prefix . '_' : '') . $timestamp . '_' . $safeName . '.' . $extension;

        $destination = $destDir . '/' . $newFilename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Error al mover el archivo al destino.'];
        }

        // Set proper permissions
        chmod($destination, 0644);

        // Build relative path for database storage
        $relativePath = (self::STORAGE_PATHS[$context] ?? $context) . '/' . $newFilename;

        return [
            'success'  => true,
            'filename' => $newFilename,
            'path'     => $relativePath,
            'size'     => $file['size'],
            'type'     => $mimeType,
        ];
    }

    /**
     * Upload multiple files from $_FILES input (array format)
     *
     * @param array  $files   The $_FILES['fieldname'] array with multiple files
     * @param string $context Storage context
     * @param string $category Validation category
     * @param string $prefix  Optional filename prefix
     * @return array{uploaded: array, errors: array}
     */
    public function uploadMultiple(array $files, string $context, string $category = 'images', string $prefix = ''): array
    {
        $results = ['uploaded' => [], 'errors' => []];

        if (empty($files['name']) || !is_array($files['name'])) {
            return $results;
        }

        $total = count($files['name']);

        for ($i = 0; $i < $total; $i++) {
            if (empty($files['name'][$i])) {
                continue;
            }

            $singleFile = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];

            $result = $this->uploadFile($singleFile, $context, $category, $prefix);

            if ($result['success']) {
                $results['uploaded'][] = $result;
            } else {
                $results['errors'][] = [
                    'file'  => $files['name'][$i],
                    'error' => $result['error'],
                ];
            }
        }

        return $results;
    }

    /**
     * Delete a file from storage
     *
     * @param string $relativePath Path relative to storage root (as stored in DB)
     * @return bool
     */
    public function deleteFile(string $relativePath): bool
    {
        $fullPath = $this->storageRoot . '/' . $relativePath;

        // Prevent directory traversal
        $realPath = realpath($fullPath);
        if (!$realPath || !str_starts_with($realPath, $this->storageRoot)) {
            return false;
        }

        if (file_exists($realPath) && is_file($realPath)) {
            return unlink($realPath);
        }

        return false;
    }

    /**
     * Get full filesystem path for a relative storage path
     *
     * @param string $relativePath Path as stored in database
     * @return string|null
     */
    public function getFullPath(string $relativePath): ?string
    {
        $fullPath = $this->storageRoot . '/' . $relativePath;
        $realPath = realpath($fullPath);

        if (!$realPath || !str_starts_with($realPath, $this->storageRoot)) {
            return null;
        }

        return $realPath;
    }

    /**
     * Check if a stored file exists
     */
    public function fileExists(string $relativePath): bool
    {
        $fullPath = $this->getFullPath($relativePath);
        return $fullPath !== null && file_exists($fullPath);
    }

    /**
     * Get the absolute directory path for a storage context
     */
    public function getStoragePath(string $context): ?string
    {
        if (!isset(self::STORAGE_PATHS[$context])) {
            return null;
        }

        return $this->storageRoot . '/' . self::STORAGE_PATHS[$context];
    }

    /**
     * Get the storage root directory
     */
    public function getStorageRoot(): string
    {
        return $this->storageRoot;
    }

    // ─── Private helpers ─────────────────────────────────────

    private function isAllowedType(string $mimeType, string $category): bool
    {
        $allowed = self::ALLOWED_TYPES[$category] ?? [];
        return in_array($mimeType, $allowed, true);
    }

    private function sanitizeFilename(string $filename): string
    {
        // Remove extension first
        $name = pathinfo($filename, PATHINFO_FILENAME);
        // Only allow safe characters
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        // Collapse multiple underscores
        $name = preg_replace('/_+/', '_', $name);
        // Trim and limit length
        $name = trim($name, '_.');
        return substr($name, 0, 100) ?: 'file';
    }

    /**
     * Get safe file extension based on MIME type (not user-provided extension)
     */
    private function getSafeExtension(string $originalName, string $mimeType): string
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        ];

        return $mimeToExt[$mimeType] ?? pathinfo($originalName, PATHINFO_EXTENSION) ?: 'bin';
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo del servidor.',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo del formulario.',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'No se encontró el directorio temporal del servidor.',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.',
            default               => 'Error desconocido al subir el archivo.',
        };
    }
}
