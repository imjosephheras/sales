<?php
/**
 * ============================================================
 * DATE HELPER
 * All date-related functions
 * ============================================================
 */

/**
 * Format date in Spanish
 */
function formatDateES($date) {
    $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    $timestamp = strtotime($date);
    $dia = date('j', $timestamp);
    $mes = $meses[(int)date('n', $timestamp)];
    $anio = date('Y', $timestamp);
    
    return "$dia de $mes de $anio";
}

/**
 * Format time in 12-hour format
 */
function formatTime12h($time) {
    if (empty($time)) return '';
    return date('g:i A', strtotime($time));
}

/**
 * Format time in 24-hour format
 */
function formatTime24h($time) {
    if (empty($time)) return '';
    return date('H:i', strtotime($time));
}

/**
 * Get days in month
 */
function getDaysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

/**
 * Get first day of month (0=Sunday, 6=Saturday)
 */
function getFirstDayOfMonth($month, $year) {
    return date('w', strtotime("$year-$month-01"));
}

/**
 * Get day name in Spanish
 */
function getDayNameES($dayNumber) {
    $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return $days[$dayNumber] ?? '';
}

/**
 * Get month name in Spanish
 */
function getMonthNameES($monthNumber) {
    $months = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    return $months[$monthNumber] ?? '';
}

/**
 * Check if date is today
 */
function isToday($date) {
    return $date === date('Y-m-d');
}

/**
 * Check if date is in past
 */
function isPast($date) {
    return strtotime($date) < strtotime(date('Y-m-d'));
}

/**
 * Check if date is in future
 */
function isFuture($date) {
    return strtotime($date) > strtotime(date('Y-m-d'));
}

/**
 * Get relative time (e.g., "2 days ago")
 */
function getRelativeTime($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M j, Y', $timestamp);
}