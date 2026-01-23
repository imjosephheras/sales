<?php
/**
 * ============================================================
 * VIEW HELPER
 * Functions for rendering views
 * ============================================================
 */

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Include a view component
 */
function component($name, $data = []) {
    extract($data);
    $componentPath = VIEWS_PATH . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $name . '.php';
    
    if (file_exists($componentPath)) {
        include $componentPath;
    } else {
        if (ENVIRONMENT === 'development') {
            echo "<!-- Component not found: $name -->";
        }
    }
}

/**
 * Include a partial view
 */
function partial($name, $data = []) {
    extract($data);
    $partialPath = VIEWS_PATH . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . $name . '.php';
    
    if (file_exists($partialPath)) {
        include $partialPath;
    } else {
        if (ENVIRONMENT === 'development') {
            echo "<!-- Partial not found: $name -->";
        }
    }
}

/**
 * Generate asset URL
 */
function asset($path) {
    // Get the base URL dynamically
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname($scriptName);
    
    // Clean up the path
    if ($baseDir === '/' || $baseDir === '\\') {
        $baseDir = '';
    }
    
    return $baseDir . '/public/' . ltrim($path, '/');
}

/**
 * Generate URL
 */
function url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . '/' . ltrim($path, '/');
}

/**
 * Check if current page
 */
function isCurrentPage($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page;
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Pluralize word
 */
function pluralize($count, $singular, $plural = null) {
    if ($count == 1) {
        return $singular;
    }
    return $plural ?? $singular . 's';
}

/**
 * Generate random color
 */
function randomColor() {
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

/**
 * Check if mobile device
 */
function isMobile() {
    return preg_match('/Mobile|Android|iPhone/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
}