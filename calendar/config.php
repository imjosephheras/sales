<?php
/**
 * ============================================================
 * CONFIGURATION FILE - SIMPLIFIED (NO AUTH)
 * ============================================================
 */

// ============================================================
// SESSION MANAGEMENT
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set a default user ID (since there's no login)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Default user
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'Administrator';
}

// ============================================================
// TIMEZONE
// ============================================================
date_default_timezone_set('America/Chicago');

// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'calendar_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// CALENDAR CONFIGURATION
// ============================================================
define('CALENDAR_START_YEAR', 2015);
define('CALENDAR_END_YEAR', 2100);

// ============================================================
// PATH CONFIGURATION
// ============================================================
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('MODELS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'Models');
define('VIEWS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'Views');
define('CONTROLLERS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'Controllers');
define('HELPERS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'Helpers');
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');
define('LOGS_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs');

// ============================================================
// ENVIRONMENT CONFIGURATION
// ============================================================
define('ENVIRONMENT', 'development'); // 'development' or 'production'

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . DIRECTORY_SEPARATOR . 'php-errors.log');
}

// ============================================================
// AUTOLOADER
// ============================================================
spl_autoload_register(function($class_name) {
    $locations = [
        MODELS_PATH . DIRECTORY_SEPARATOR . $class_name . '.php',
        CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $class_name . '.php',
        HELPERS_PATH . DIRECTORY_SEPARATOR . $class_name . '.php',
        BASE_PATH . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class_name . '.php',
    ];
    
    foreach ($locations as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ============================================================
// LOAD HELPERS (Only the ones we need)
// ============================================================
require_once HELPERS_PATH . DIRECTORY_SEPARATOR . 'DateHelper.php';
require_once HELPERS_PATH . DIRECTORY_SEPARATOR . 'ValidationHelper.php';
require_once HELPERS_PATH . DIRECTORY_SEPARATOR . 'ViewHelper.php';

// ============================================================
// SIMPLE HELPER FUNCTIONS (replacing SessionHelper)
// ============================================================

/**
 * Get current user ID (always returns 1 since no auth)
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 1;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    return [
        'user_id' => $_SESSION['user_id'] ?? 1,
        'username' => $_SESSION['username'] ?? 'admin',
        'full_name' => $_SESSION['full_name'] ?? 'Administrator',
        'email' => 'admin@calendar.com',
        'timezone' => 'America/Chicago'
    ];
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message (for views)
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        component('flash-message', ['flash' => $flash]);
    }
}

/**
 * No-op function (was requireAuth, now does nothing)
 */
function requireAuth() {
    // Do nothing - no authentication needed
    return true;
}

/**
 * Check if logged in (always true)
 */
function isLoggedIn() {
    return true;
}

// ============================================================
// STATUS CONSTANTS
// ============================================================
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_CANCELLED', 'cancelled');
define('STATUS_COMPLETED', 'completed');

// ============================================================
// PRIORITY CONSTANTS
// ============================================================
define('PRIORITY_LOW', 'low');
define('PRIORITY_NORMAL', 'normal');
define('PRIORITY_HIGH', 'high');
define('PRIORITY_URGENT', 'urgent');