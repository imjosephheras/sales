<?php
/**
 * Test script to check database contents
 */

require_once 'contract_generator/contract_generator/config/db_config.php';

echo "=== Testing Database Connection ===\n\n";

try {
    // Count all requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM requests");
    $total = $stmt->fetch()['total'];
    echo "Total requests in database: $total\n\n";

    // Count by status
    echo "=== Requests by Status ===\n";
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM requests GROUP BY status");
    while ($row = $stmt->fetch()) {
        echo "{$row['status']}: {$row['count']}\n";
    }
    echo "\n";

    // Show recent pending requests
    echo "=== Recent Pending Requests ===\n";
    $stmt = $pdo->query("
        SELECT id, Company_Name, Service_Type, Priority, status, created_at
        FROM requests
        WHERE status IN ('pending', 'in_progress')
        ORDER BY created_at DESC
        LIMIT 5
    ");

    $requests = $stmt->fetchAll();
    if (count($requests) > 0) {
        foreach ($requests as $req) {
            echo "ID: {$req['id']} | Company: {$req['Company_Name']} | Service: {$req['Service_Type']} | Priority: {$req['Priority']} | Status: {$req['status']} | Created: {$req['created_at']}\n";
        }
    } else {
        echo "No pending requests found.\n";
    }
    echo "\n";

    // Show all requests (just count and latest)
    echo "=== Latest 10 Requests (any status) ===\n";
    $stmt = $pdo->query("
        SELECT id, Company_Name, Service_Type, status, created_at
        FROM requests
        ORDER BY created_at DESC
        LIMIT 10
    ");

    $all_requests = $stmt->fetchAll();
    foreach ($all_requests as $req) {
        echo "ID: {$req['id']} | Company: {$req['Company_Name']} | Status: {$req['status']} | Created: {$req['created_at']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
