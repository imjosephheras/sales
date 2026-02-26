<?php
/**
 * Get all attachments for a specific billing document
 */
require_once __DIR__ . '/../config/db_config.php';
header('Content-Type: application/json');

try {
    $document_id   = $_GET['document_id'] ?? null;
    $document_type = $_GET['document_type'] ?? null;

    if (!$document_id) {
        echo json_encode(['success' => false, 'error' => 'document_id is required']);
        exit;
    }

    $sql = "SELECT * FROM document_attachments WHERE document_id = :document_id";
    $params = ['document_id' => intval($document_id)];

    if ($document_type) {
        $sql .= " AND document_type = :document_type";
        $params['document_type'] = $document_type;
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates
    foreach ($attachments as &$att) {
        if ($att['created_at']) {
            $att['created_at_formatted'] = date('M d, Y g:i A', strtotime($att['created_at']));
        }
    }

    echo json_encode([
        'success' => true,
        'data'    => $attachments,
        'count'   => count($attachments)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
