<?php
/**
 * Save Product - Backend endpoint for Manage Products
 * Handles creating new products with image upload (file or base64 from clipboard).
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$pdo = getDBConnection();

// Ensure products table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `products` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `name`       VARCHAR(255) NOT NULL,
        `image_path` VARCHAR(500) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['product_name'] ?? '');

if ($name === '') {
    echo json_encode(['success' => false, 'message' => 'Product name is required']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$imagePath = '';

// Option 1: Base64 image from clipboard paste
$base64Image = $_POST['product_image_base64'] ?? '';
if ($base64Image !== '') {
    // Extract mime type and data
    if (preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,(.+)$/i', $base64Image, $matches)) {
        $ext = strtolower($matches[1] === 'jpeg' ? 'jpg' : $matches[1]);
        $data = base64_decode($matches[2]);
        if ($data === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid image data']);
            exit;
        }
        $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $fullPath = $uploadDir . $filename;
        file_put_contents($fullPath, $data);
        $imagePath = 'uploads/products/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid base64 image format']);
        exit;
    }
}

// Option 2: Traditional file upload
if ($imagePath === '' && isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['product_image'];
    $allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: PNG, JPG, GIF, WEBP']);
        exit;
    }

    $ext = match ($mimeType) {
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => 'png',
    };

    $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $fullPath = $uploadDir . $filename;
    move_uploaded_file($file['tmp_name'], $fullPath);
    $imagePath = 'uploads/products/' . $filename;
}

if ($imagePath === '') {
    echo json_encode(['success' => false, 'message' => 'Product image is required']);
    exit;
}

// Insert into database
$stmt = $pdo->prepare("INSERT INTO `products` (`name`, `image_path`) VALUES (:name, :image_path)");
$stmt->execute([
    ':name' => $name,
    ':image_path' => $imagePath,
]);

$productId = $pdo->lastInsertId();

echo json_encode([
    'success' => true,
    'message' => 'Product saved successfully',
    'product' => [
        'id' => $productId,
        'name' => $name,
        'image_path' => $imagePath,
    ]
]);
