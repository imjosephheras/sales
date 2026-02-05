<?php
/**
 * Configuration - BASIC
 */

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'Administrator';
}

// Timezone
date_default_timezone_set('America/Chicago');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'calendar_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Paths
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('MODELS_PATH', APP_PATH . '/Models');
define('VIEWS_PATH', APP_PATH . '/Views');
define('CONTROLLERS_PATH', APP_PATH . '/Controllers');
define('HELPERS_PATH', APP_PATH . '/Helpers');

// Calendar range
define('CALENDAR_START_YEAR', 2015);
define('CALENDAR_END_YEAR', 2100);

// Environment
define('ENVIRONMENT', 'development');
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Autoloader
spl_autoload_register(function($class) {
    $paths = [
        MODELS_PATH . "/$class.php",
        CONTROLLERS_PATH . "/$class.php",
        HELPERS_PATH . "/$class.php",
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load helpers
require_once HELPERS_PATH . '/DateHelper.php';
require_once HELPERS_PATH . '/ValidationHelper.php';
require_once HELPERS_PATH . '/ViewHelper.php';

// Simple helper functions
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 1;
}

function getCurrentUser() {
    return [
        'user_id'   => $_SESSION['user_id'] ?? 1,
        'username'  => $_SESSION['username'] ?? 'admin',
        'full_name' => $_SESSION['full_name'] ?? 'Administrator',
    ];
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function requireAuth() {
    return true;
}
