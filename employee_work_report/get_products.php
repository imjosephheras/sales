<?php
/**
 * Get Products - Backend endpoint to list all products
 * Returns JSON array of products from the database.
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

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `name` LIKE :search ORDER BY `created_at` DESC");
    $stmt->execute([':search' => '%' . $search . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM `products` ORDER BY `created_at` DESC");
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'products' => $products,
]);
