<?php
/**
 * Get pending billing documents
 */
require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    $search = $_GET['search'] ?? '';

    $sql = "SELECT * FROM billing_documents WHERE status = 'pending'";
    $params = [];

    if ($search) {
        $sql .= " AND (order_number LIKE :search OR client_name LIKE :search2 OR company_name LIKE :search3)";
        $params['search'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "%$search%";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $documents]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
