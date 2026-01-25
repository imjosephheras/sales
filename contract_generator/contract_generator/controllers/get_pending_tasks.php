<?php
/**
 * GET PENDING TASKS CONTROLLER
 * Returns pending tasks with associated event information
 * Note: This connects to calendar_system database (separate from form database)
 */

header('Content-Type: application/json');

// Ensure we always output JSON, even on fatal errors
ob_start();

try {
    // Connect to calendar_system database
    $pdo = new PDO(
        "mysql:host=localhost;dbname=calendar_system;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // First check if the tables exist
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'tasks'");
    if ($tableCheck->rowCount() === 0) {
        // Tasks table doesn't exist - return empty but successful response
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'data' => [],
            'count' => 0,
            'note' => 'Tasks table not found in calendar_system'
        ]);
        exit;
    }

    // Get pending tasks with event and category information
    // Use IFNULL to handle potentially missing columns gracefully
    $sql = "SELECT
                t.task_id,
                t.event_id,
                t.title,
                t.description,
                t.due_date,
                t.priority,
                e.title as event_title,
                e.status as event_status,
                e.client,
                IFNULL(c.category_name, '') as category_name,
                IFNULL(c.color_hex, '#6c757d') as category_color,
                IFNULL(c.icon, '📋') as category_icon
            FROM tasks t
            LEFT JOIN events e ON t.event_id = e.event_id
            LEFT JOIN event_categories c ON e.category_id = c.category_id
            WHERE t.is_completed = 0
            ORDER BY
                FIELD(t.priority, 'urgent', 'high', 'normal', 'low'),
                t.due_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates
    foreach ($tasks as &$task) {
        if ($task['due_date']) {
            $task['due_date_formatted'] = date('M d, Y', strtotime($task['due_date']));
        } else {
            $task['due_date_formatted'] = 'No due date';
        }

        // Ensure description is not null
        $task['description'] = $task['description'] ?? 'No description';
        $task['client'] = $task['client'] ?? 'No client';
    }

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $tasks,
        'count' => count($tasks)
    ]);

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
