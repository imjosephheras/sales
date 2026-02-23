<?php
/**
 * Delete Product - Backend endpoint to remove a product
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$pdo = getDBConnection();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Get image path before deleting
$stmt = $pdo->prepare("SELECT `image_path` FROM `products` WHERE `id` = :id");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Delete the image file
$imagePath = __DIR__ . '/../' . $product['image_path'];
if (file_exists($imagePath)) {
    unlink($imagePath);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM `products` WHERE `id` = :id");
$stmt->execute([':id' => $id]);

echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
