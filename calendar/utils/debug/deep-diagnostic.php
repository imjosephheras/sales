<?php
/**
 * ============================================================
 * DEEP CATEGORY DIAGNOSTIC
 * Encuentra EXACTAMENTE por qué no se cargan las categorías
 * ============================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Deep Category Diagnostic</h1>";
echo "<hr>";

// PASO 1: Verificar config.php
echo "<h2>1. Loading config.php</h2>";
if (file_exists('config.php')) {
    echo "<p style='color: green;'>✓ config.php exists</p>";
    require_once '../../config.php';
    echo "<p style='color: green;'>✓ config.php loaded</p>";
} else {
    echo "<p style='color: red;'>✗ config.php NOT FOUND!</p>";
    die();
}

echo "<hr>";

// PASO 2: Verificar sesión
echo "<h2>2. Session Check</h2>";
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    echo "<p style='color: green;'>✓ Logged in - User ID: <strong>$userId</strong></p>";
} else {
    echo "<p style='color: red;'>✗ NOT logged in</p>";
    echo "<p><a href='login.php'>Login first</a></p>";
    die();
}

echo "<hr>";

// PASO 3: Verificar Database.php
echo "<h2>3. Database.php Check</h2>";
if (class_exists('Database')) {
    echo "<p style='color: green;'>✓ Database class exists</p>";
    
    try {
        $db = Database::getInstance();
        echo "<p style='color: green;'>✓ Database instance created</p>";
        
        $conn = $db->getConnection();
        echo "<p style='color: green;'>✓ Database connection obtained</p>";
        
        // Test query
        $testQuery = "SELECT COUNT(*) as total FROM event_categories WHERE user_id = :user_id";
        $stmt = $conn->prepare($testQuery);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo "<p style='color: green;'>✓ Direct query works - Found <strong>{$result['total']}</strong> categories</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database class NOT FOUND!</p>";
    echo "<p>Checking if Database.php exists...</p>";
    
    $possiblePaths = [
        'Database.php',
        'classes/Database.php',
        'includes/Database.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "<p>Found at: <strong>$path</strong></p>";
            echo "<p style='color: orange;'>⚠ File exists but class not loaded. Check require_once in config.php</p>";
        }
    }
}

echo "<hr>";

// PASO 4: Verificar Category.php
echo "<h2>4. Category.php Check</h2>";
if (class_exists('Category')) {
    echo "<p style='color: green;'>✓ Category class exists</p>";
    
    try {
        $category = new Category();
        echo "<p style='color: green;'>✓ Category instance created</p>";
        
        // Test getAllByUser
        echo "<p>Calling getAllByUser($userId)...</p>";
        $categories = $category->getAllByUser($userId);
        
        echo "<p>Result type: <strong>" . gettype($categories) . "</strong></p>";
        echo "<p>Is array: <strong>" . (is_array($categories) ? 'YES' : 'NO') . "</strong></p>";
        echo "<p>Count: <strong>" . count($categories) . "</strong></p>";
        
        if (count($categories) > 0) {
            echo "<p style='color: green;'>✓ Categories loaded successfully!</p>";
            
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin-top: 20px;'>";
            echo "<tr><th>ID</th><th>Icon</th><th>Name</th><th>Color</th><th>User ID</th></tr>";
            foreach ($categories as $cat) {
                echo "<tr>";
                echo "<td>{$cat['category_id']}</td>";
                echo "<td style='font-size: 20px;'>{$cat['icon']}</td>";
                echo "<td>{$cat['category_name']}</td>";
                echo "<td><div style='background: {$cat['color_hex']}; width: 40px; height: 20px;'></div></td>";
                echo "<td>{$cat['user_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>✗ getAllByUser() returned empty array</p>";
            
            // Debug the method itself
            echo "<h3>Debugging getAllByUser() method</h3>";
            
            // Try direct query
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            $query = "SELECT * FROM event_categories WHERE user_id = :user_id ORDER BY is_default DESC, category_name ASC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $directResult = $stmt->fetchAll();
            
            echo "<p>Direct query result: <strong>" . count($directResult) . "</strong> rows</p>";
            
            if (count($directResult) > 0) {
                echo "<p style='color: orange;'>⚠ Direct query WORKS but Category::getAllByUser() doesn't!</p>";
                echo "<p><strong>PROBLEM:</strong> There's an issue in the Category class method.</p>";
                
                echo "<h4>Direct Query Result:</h4>";
                echo "<pre>" . print_r($directResult, true) . "</pre>";
            } else {
                echo "<p style='color: red;'>✗ Even direct query returns empty!</p>";
                echo "<p><strong>PROBLEM:</strong> No categories exist for user_id $userId</p>";
                
                // Check if categories exist for OTHER users
                $query2 = "SELECT user_id, COUNT(*) as total FROM event_categories GROUP BY user_id";
                $stmt2 = $conn->query($query2);
                $others = $stmt2->fetchAll();
                
                if (!empty($others)) {
                    echo "<h4>Categories found for other users:</h4>";
                    echo "<ul>";
                    foreach ($others as $other) {
                        echo "<li>User ID {$other['user_id']}: {$other['total']} categories</li>";
                    }
                    echo "</ul>";
                    echo "<p style='color: orange;'>⚠ Categories exist but for WRONG user_id!</p>";
                    echo "<p><a href='fix_category_userid.php' style='padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>Fix User ID Mismatch</a></p>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Category error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Category class NOT FOUND!</p>";
    echo "<p>Checking if Category.php exists...</p>";
    
    $possiblePaths = [
        'Category.php',
        'category.php',
        'classes/Category.php',
        'includes/Category.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "<p>Found at: <strong>$path</strong></p>";
            echo "<p style='color: orange;'>⚠ File exists but class not loaded. Check require_once or autoloader.</p>";
        }
    }
}

echo "<hr>";

// PASO 5: Verificar autoloader
echo "<h2>5. Autoloader Check</h2>";
$autoloadFunctions = spl_autoload_functions();
if ($autoloadFunctions) {
    echo "<p style='color: green;'>✓ Autoloader registered</p>";
    echo "<pre>" . print_r($autoloadFunctions, true) . "</pre>";
} else {
    echo "<p style='color: orange;'>⚠ No autoloader found</p>";
}

echo "<hr>";

// PASO 6: Test completo como en index.php
echo "<h2>6. Full Test (Simulating index.php)</h2>";
echo "<pre>";
echo "Code:\n";
echo "\$category = new Category();\n";
echo "\$categories = \$category->getAllByUser(\$userId);\n";
echo "count(\$categories): ";
try {
    $testCategory = new Category();
    $testCategories = $testCategory->getAllByUser($userId);
    echo count($testCategories);
    
    if (count($testCategories) > 0) {
        echo "\n\nFirst category:\n";
        print_r($testCategories[0]);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
echo "</pre>";

echo "<hr>";

// RESUMEN
echo "<h2>📊 SUMMARY</h2>";
echo "<p><strong>User ID:</strong> $userId</p>";

if (isset($categories) && count($categories) > 0) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ EVERYTHING WORKS!</p>";
    echo "<p>Categories are loading correctly. The problem must be in index.php caching or JavaScript.</p>";
    echo "<p><strong>Solution:</strong> Clear browser cache completely and try again.</p>";
} elseif (isset($directResult) && count($directResult) > 0) {
    echo "<p style='color: orange; font-size: 18px; font-weight: bold;'>⚠ CATEGORY CLASS ISSUE</p>";
    echo "<p>Categories exist in database but Category::getAllByUser() fails.</p>";
    echo "<p><strong>Solution:</strong> Check Category.php for errors or replace with a working version.</p>";
} elseif (isset($others) && !empty($others)) {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>✗ USER ID MISMATCH</p>";
    echo "<p>Categories exist but for wrong user_id.</p>";
    echo "<p><strong>Solution:</strong> <a href='fix_category_userid.php'>Fix user_id mismatch</a></p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>✗ NO CATEGORIES IN DATABASE</p>";
    echo "<p>No categories exist at all.</p>";
    echo "<p><strong>Solution:</strong> <a href='create_categories.php'>Create categories</a></p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Back to Calendar</a></p>";
?>

<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        max-width: 1000px;
        margin: 40px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #333;
        border-bottom: 3px solid #3b82f6;
        padding-bottom: 10px;
    }
    h2 {
        color: #555;
        margin-top: 30px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 5px;
    }
    table {
        width: 100%;
        background: white;
        margin-top: 20px;
    }
    th {
        background: #3b82f6;
        color: white;
        padding: 12px;
    }
    td {
        padding: 10px;
    }
    pre {
        background: #2d2d2d;
        color: #f8f8f2;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
    }
    a {
        color: #3b82f6;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>