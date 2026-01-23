<?php
/**
 * DIAGNOSTIC TEST FILE
 * Check if basic PHP works
 */

echo "PHP is working! ✅<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test 1: Check if config.php loads
echo "<h3>Test 1: Loading config.php</h3>";
try {
    require_once 'config.php';
    echo "✅ config.php loaded successfully<br>";
    echo "BASE_PATH: " . BASE_PATH . "<br>";
    echo "APP_PATH: " . APP_PATH . "<br>";
    echo "HELPERS_PATH: " . HELPERS_PATH . "<br>";
} catch (Exception $e) {
    echo "❌ Error loading config.php: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check if Helpers exist
echo "<h3>Test 2: Checking Helpers</h3>";
$helpers = [
    'SessionHelper.php',
    'DateHelper.php',
    'ValidationHelper.php',
    'ViewHelper.php'
];

foreach ($helpers as $helper) {
    $path = HELPERS_PATH . DIRECTORY_SEPARATOR . $helper;
    if (file_exists($path)) {
        echo "✅ $helper found<br>";
    } else {
        echo "❌ $helper NOT FOUND at: $path<br>";
    }
}

// Test 3: Check if Controllers exist
echo "<h3>Test 3: Checking Controllers</h3>";
$controllers = [
    'CalendarController.php',
    'EventController.php',
    'CategoryController.php',
    'AuthController.php'
];

foreach ($controllers as $controller) {
    $path = CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $controller;
    if (file_exists($path)) {
        echo "✅ $controller found<br>";
    } else {
        echo "❌ $controller NOT FOUND at: $path<br>";
    }
}

// Test 4: Check if Models exist
echo "<h3>Test 4: Checking Models</h3>";
$models = [
    'Database.php',
    'Event.php',
    'Category.php',
    'User.php'
];

foreach ($models as $model) {
    $path = MODELS_PATH . DIRECTORY_SEPARATOR . $model;
    if (file_exists($path)) {
        echo "✅ $model found<br>";
    } else {
        echo "❌ $model NOT FOUND at: $path<br>";
    }
}

// Test 5: Check if Views exist
echo "<h3>Test 5: Checking Views</h3>";
$views = [
    'auth/login.php',
    'layouts/auth-layout.php',
    'calendar/index.php'
];

foreach ($views as $view) {
    $path = VIEWS_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view);
    if (file_exists($path)) {
        echo "✅ $view found<br>";
    } else {
        echo "❌ $view NOT FOUND at: $path<br>";
    }
}

// Test 6: Check session
echo "<h3>Test 6: Session Test</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session is active<br>";
} else {
    echo "⚠️ Session is not active<br>";
}

// Test 7: Check functions
echo "<h3>Test 7: Helper Functions</h3>";
if (function_exists('isLoggedIn')) {
    echo "✅ isLoggedIn() function exists<br>";
} else {
    echo "❌ isLoggedIn() function NOT FOUND<br>";
}

if (function_exists('component')) {
    echo "✅ component() function exists<br>";
} else {
    echo "❌ component() function NOT FOUND<br>";
}

// Test 8: Check storage writable
echo "<h3>Test 8: Storage Permissions</h3>";
$storageLog = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'test.txt';
if (is_writable(dirname($storageLog))) {
    echo "✅ Storage/logs is writable<br>";
    file_put_contents($storageLog, "Test write at " . date('Y-m-d H:i:s'));
    echo "✅ Test file created: $storageLog<br>";
} else {
    echo "❌ Storage/logs is NOT writable<br>";
}

echo "<h3>✅ All Tests Complete!</h3>";
echo "<p>If you see this, PHP is working correctly.</p>";
echo "<p><a href='login.php'>Try login.php now</a></p>";