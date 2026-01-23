<?php
/**
 * ============================================================
 * CALENDAR DATA API ENDPOINT
 * GET all data needed for a calendar month
 * Returns events, categories, and statistics
 * ============================================================
 */

header('Content-Type: application/json');

require_once '../config.php';

// Require authentication
requireAuth();

$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid month']);
    exit;
}

if ($year < CALENDAR_START_YEAR || $year > CALENDAR_END_YEAR) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid year']);
    exit;
}

try {
    $userId = getCurrentUserId();
    $event = new Event();
    $category = new Category();
    
    // Get events for the month
    $events = $event->getByMonth($userId, $year, $month);
    
    // Get categories
    $categories = $category->getAllByUser($userId);
    
    // Get today's events
    $todayEvents = $event->getToday($userId);
    
    // Calculate statistics
    $stats = [
        'total_events' => count($events),
        'completed' => count(array_filter($events, fn($e) => $e['status'] === 'completed')),
        'pending' => count(array_filter($events, fn($e) => $e['status'] === 'pending')),
        'confirmed' => count(array_filter($events, fn($e) => $e['status'] === 'confirmed'))
    ];
    
    // Group by category
    $by_category = [];
    foreach ($events as $evt) {
        $cat_name = $evt['category_name'] ?? 'Uncategorized';
        if (!isset($by_category[$cat_name])) {
            $by_category[$cat_name] = 0;
        }
        $by_category[$cat_name]++;
    }
    
    // Build response
    $response = [
        'success' => true,
        'month' => $month,
        'year' => $year,
        'month_name' => date('F', strtotime("$year-$month-01")),
        'events' => $events,
        'categories' => $categories,
        'today_events' => $todayEvents,
        'statistics' => $stats,
        'by_category' => $by_category,
        'calendar_info' => [
            'first_day' => getFirstDayOfMonth($month, $year),
            'days_in_month' => getDaysInMonth($month, $year),
            'is_current_month' => ($month == date('n') && $year == date('Y'))
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error getting calendar data: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => ENVIRONMENT === 'development' ? $e->getMessage() : null
    ]);
}