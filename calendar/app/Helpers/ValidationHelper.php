<?php
/**
 * ============================================================
 * VALIDATION HELPER
 * All validation functions
 * ============================================================
 */

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate date
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validate time
 */
function validateTime($time) {
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time);
}

/**
 * Validate required field
 */
function validateRequired($value) {
    return !empty(trim($value));
}

/**
 * Validate string length
 */
function validateLength($string, $min = 0, $max = PHP_INT_MAX) {
    $length = strlen($string);
    return $length >= $min && $length <= $max;
}

/**
 * Validate integer
 */
function validateInteger($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

/**
 * Validate positive integer
 */
function validatePositiveInt($value) {
    return validateInteger($value) && $value > 0;
}

/**
 * Validate URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Validate phone number (basic)
 */
function validatePhone($phone) {
    return preg_match('/^[\d\s\-\+\(\)]+$/', $phone);
}

/**
 * Validate color hex
 */
function validateColorHex($color) {
    return preg_match('/^#[a-fA-F0-9]{6}$/', $color);
}

/**
 * Validate in array
 */
function validateInArray($value, $array) {
    return in_array($value, $array);
}

/**
 * Validate status
 */
function validateStatus($status) {
    $validStatuses = [
        STATUS_PENDING,
        STATUS_CONFIRMED,
        STATUS_CANCELLED,
        STATUS_COMPLETED
    ];
    return validateInArray($status, $validStatuses);
}

/**
 * Validate priority
 */
function validatePriority($priority) {
    $validPriorities = [
        PRIORITY_LOW,
        PRIORITY_NORMAL,
        PRIORITY_HIGH,
        PRIORITY_URGENT
    ];
    return validateInArray($priority, $validPriorities);
}