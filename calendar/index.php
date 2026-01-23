<?php
/**
 * ============================================================
 * CALENDAR ENTRY POINT - SIMPLIFIED (NO AUTH)
 * ============================================================
 */

require_once 'config.php';
require_once 'app/Controllers/CalendarController.php';

// Get month and year from URL or use current
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month (1-12)
if ($month < 1 || $month > 12) {
    $month = date('n');
}

// Validate year
if ($year < CALENDAR_START_YEAR || $year > CALENDAR_END_YEAR) {
    $year = date('Y');
}

// Initialize controller and display calendar
$controller = new CalendarController();
$controller->index($month, $year);