<?php
/**
 * Get completed billing documents (history)
 */
require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    $search = $_GET['search'] ?? '';

    $sql = "SELECT * FROM billing_documents WHERE status = 'completed'";
    $params = [];

    if ($search) {
        $sql .= " AND (order_number LIKE :search OR client_name LIKE :search2 OR company_name LIKE :search3)";
        $params['search'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "%$search%";
    }

    $sql .= " ORDER BY completed_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $documents]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
