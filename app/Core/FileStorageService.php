<?php
/**
 * FileStorageService - Centralized file upload and storage management
 *
 * Handles file validation, sanitization, storage, and retrieval
 * for all modules (form photos, work reports, profile photos, documents).
 *
 * Supports two storage disks:
 *   - 'local' : files stored on the local filesystem (default)
 *   - 'ftp'   : files uploaded to a remote FTP server
 */
class FileStorageService
{
    /** Base storage directory (local) */
    private string $storageRoot;

    /** Active storage disk: 'local' or 'ftp' */
    private string $disk;

    /** FTP connection resource */
    private $ftpConn = null;

    /** FTP configuration */
    private array $ftpConfig = [];

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
            'text/csv',
            'text/plain',
            'image/jpeg',
            'image/png',
        ],
    ];

    /** Max file sizes in bytes per category */
    private const MAX_SIZES = [
        'images'    => 10 * 1024 * 1024,  // 10 MB
        'documents' => 20 * 1024 * 1024,  // 20 MB
    ];

    /** Subdirectory mapping for each storage context */
    private const STORAGE_PATHS = [
        'form_photos'        => 'sales/fotos_forms',
        'work_report_photos' => 'sales/work_report_photos',
        'profile_photos'     => 'sales/profile_photos',
        'documents'          => 'sales/files',
        'final_pdfs'         => 'sales/final_pdfs',
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

        // Determine storage disk from environment
        $this->disk = strtolower(getenv('STORAGE_DISK') ?: 'local');

        if ($this->disk === 'ftp') {
            $this->ftpConfig = [
                'host'     => getenv('FTP_HOST') ?: '',
                'port'     => (int)(getenv('FTP_PORT') ?: 21),
                'username' => getenv('FTP_USERNAME') ?: '',
                'password' => getenv('FTP_PASSWORD') ?: '',
                'root'     => rtrim(getenv('FTP_ROOT') ?: '/', '/'),
                'passive'  => filter_var(getenv('FTP_PASSIVE') ?: 'true', FILTER_VALIDATE_BOOLEAN),
            ];
        }
    }

    public function __destruct()
    {
        $this->ftpDisconnect();
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

        // Generate safe filename
        $extension = $this->getSafeExtension($file['name'], $mimeType);
        $safeName = $this->sanitizeFilename($file['name']);
        $timestamp = time() . '_' . bin2hex(random_bytes(4));
        $newFilename = ($prefix ? $prefix . '_' : '') . $timestamp . '_' . $safeName . '.' . $extension;

        // Build relative path for database storage
        $relativePath = (self::STORAGE_PATHS[$context] ?? $context) . '/' . $newFilename;

        // Upload based on active disk
        if ($this->disk === 'ftp') {
            $result = $this->ftpUpload($file['tmp_name'], $relativePath);
            if (!$result) {
                return ['success' => false, 'error' => 'Error al subir el archivo al servidor FTP.'];
            }
        } else {
            // Local storage
            $destDir = $this->getStoragePath($context);
            if (!$destDir) {
                return ['success' => false, 'error' => "Contexto de almacenamiento no válido: {$context}"];
            }
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $destination = $destDir . '/' . $newFilename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                return ['success' => false, 'error' => 'Error al mover el archivo al destino.'];
            }
            chmod($destination, 0644);
        }

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
        if ($this->disk === 'ftp') {
            return $this->ftpDelete($relativePath);
        }

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
        if ($this->disk === 'ftp') {
            // For FTP, return the FTP root-based path
            return $this->ftpConfig['root'] . '/' . $relativePath;
        }

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
        if ($this->disk === 'ftp') {
            return $this->ftpFileExists($relativePath);
        }

        $fullPath = $this->getFullPath($relativePath);
        return $fullPath !== null && file_exists($fullPath);
    }

    /**
     * Download a file from FTP to a local temporary path for serving
     *
     * @param string $relativePath Path as stored in database
     * @return string|null Local temp file path, or null on failure
     */
    public function downloadToTemp(string $relativePath): ?string
    {
        if ($this->disk !== 'ftp') {
            return $this->getFullPath($relativePath);
        }

        $conn = $this->ftpConnect();
        if (!$conn) {
            return null;
        }

        $remotePath = $this->ftpConfig['root'] . '/' . $relativePath;
        $tempFile = sys_get_temp_dir() . '/ftp_' . md5($relativePath) . '_' . basename($relativePath);

        if (ftp_get($conn, $tempFile, $remotePath, FTP_BINARY)) {
            return $tempFile;
        }

        return null;
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

    /**
     * Get the active storage disk name
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    // ─── FTP methods ────────────────────────────────────────────

    /**
     * Establish FTP connection
     *
     * @return resource|false FTP connection or false on failure
     */
    private function ftpConnect()
    {
        if ($this->ftpConn !== null) {
            // Test if connection is still alive
            if (@ftp_systype($this->ftpConn) !== false) {
                return $this->ftpConn;
            }
            $this->ftpDisconnect();
        }

        $conn = @ftp_connect($this->ftpConfig['host'], $this->ftpConfig['port'], 30);
        if (!$conn) {
            error_log("FTP: No se pudo conectar a {$this->ftpConfig['host']}:{$this->ftpConfig['port']}");
            return false;
        }

        if (!@ftp_login($conn, $this->ftpConfig['username'], $this->ftpConfig['password'])) {
            error_log("FTP: Autenticación fallida para usuario {$this->ftpConfig['username']}");
            ftp_close($conn);
            return false;
        }

        if ($this->ftpConfig['passive']) {
            ftp_pasv($conn, true);
        }

        $this->ftpConn = $conn;
        return $conn;
    }

    /**
     * Close FTP connection
     */
    private function ftpDisconnect(): void
    {
        if ($this->ftpConn !== null) {
            @ftp_close($this->ftpConn);
            $this->ftpConn = null;
        }
    }

    /**
     * Upload a local file to the FTP server
     */
    private function ftpUpload(string $localPath, string $relativePath): bool
    {
        $conn = $this->ftpConnect();
        if (!$conn) {
            return false;
        }

        $remotePath = $this->ftpConfig['root'] . '/' . $relativePath;
        $remoteDir = dirname($remotePath);

        // Create remote directory tree if it doesn't exist
        $this->ftpMkdirRecursive($conn, $remoteDir);

        if (ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
            return true;
        }

        error_log("FTP: Error al subir archivo a {$remotePath}");
        return false;
    }

    /**
     * Delete a file from the FTP server
     */
    private function ftpDelete(string $relativePath): bool
    {
        $conn = $this->ftpConnect();
        if (!$conn) {
            return false;
        }

        $remotePath = $this->ftpConfig['root'] . '/' . $relativePath;
        return @ftp_delete($conn, $remotePath);
    }

    /**
     * Check if a file exists on the FTP server
     */
    private function ftpFileExists(string $relativePath): bool
    {
        $conn = $this->ftpConnect();
        if (!$conn) {
            return false;
        }

        $remotePath = $this->ftpConfig['root'] . '/' . $relativePath;
        $size = @ftp_size($conn, $remotePath);
        return $size >= 0;
    }

    /**
     * Recursively create directories on the FTP server
     */
    private function ftpMkdirRecursive($conn, string $dir): void
    {
        if ($dir === '/' || $dir === '.' || $dir === '') {
            return;
        }

        // Check if directory already exists
        $originalDir = @ftp_pwd($conn);
        if (@ftp_chdir($conn, $dir)) {
            @ftp_chdir($conn, $originalDir);
            return;
        }

        // Create parent first
        $parent = dirname($dir);
        if ($parent !== $dir) {
            $this->ftpMkdirRecursive($conn, $parent);
        }

        @ftp_mkdir($conn, $dir);
        @ftp_chdir($conn, $originalDir);
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
            'text/csv'   => 'csv',
            'text/plain' => 'txt',
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
