<?php
/**
 * ============================================================
 * DEBUG CATEGORIES
 * Script para diagnosticar por qué no se cargan las categorías
 * ============================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config.php';

echo "<h1>Category Debug</h1>";
echo "<hr>";

// Verificar si estás logueado
echo "<h2>1. Session Check</h2>";
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $currentUser = getCurrentUser();
    echo "<p style='color: green;'>✓ Logged in as: <strong>{$currentUser['full_name']}</strong></p>";
    echo "<p>User ID: <strong>$userId</strong></p>";
} else {
    echo "<p style='color: red;'>✗ NOT logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    die();
}

echo "<hr>";

// Verificar conexión a base de datos
echo "<h2>2. Database Connection</h2>";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    die();
}

echo "<hr>";

// Verificar categorías directamente en BD
echo "<h2>3. Direct Database Query</h2>";
try {
    $query = "SELECT COUNT(*) as total FROM event_categories WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "<p>Categories for user_id <strong>$userId</strong>: <strong>{$result['total']}</strong></p>";
    
    if ($result['total'] == 0) {
        echo "<p style='color: red;'>⚠ No categories found for this user!</p>";
        
        // Check if categories exist for OTHER users
        $query2 = "SELECT user_id, COUNT(*) as total FROM event_categories GROUP BY user_id";
        $stmt2 = $conn->query($query2);
        $otherUsers = $stmt2->fetchAll();
        
        if (!empty($otherUsers)) {
            echo "<p>Categories found for other users:</p>";
            echo "<ul>";
            foreach ($otherUsers as $ou) {
                echo "<li>User ID {$ou['user_id']}: {$ou['total']} categories</li>";
            }
            echo "</ul>";
            echo "<p style='color: orange;'>⚠ The categories were created for a different user_id!</p>";
            echo "<p><strong>SOLUTION:</strong> Update categories to your user_id:</p>";
            echo "<pre>UPDATE event_categories SET user_id = $userId WHERE user_id != $userId;</pre>";
        }
    } else {
        echo "<p style='color: green;'>✓ Categories exist in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Query error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Verificar clase Category
echo "<h2>4. Category Class Test</h2>";
try {
    $category = new Category();
    echo "<p style='color: green;'>✓ Category class instantiated</p>";
    
    $categories = $category->getAllByUser($userId);
    echo "<p>Categories returned: <strong>" . count($categories) . "</strong></p>";
    
    if (empty($categories)) {
        echo "<p style='color: red;'>✗ getAllByUser() returned empty array</p>";
        
        // Debug the method
        echo "<p>Testing direct query...</p>";
        $query = "SELECT * FROM event_categories WHERE user_id = :user_id ORDER BY category_name ASC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $directResult = $stmt->fetchAll();
        
        echo "<p>Direct query result: " . count($directResult) . " rows</p>";
        
        if (count($directResult) > 0) {
            echo "<p style='color: orange;'>⚠ Query works, but Category::getAllByUser() doesn't!</p>";
            echo "<p>Check your Category.php file - there might be an issue in the getAllByUser() method.</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Categories loaded successfully</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Category class error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Mostrar todas las categorías
echo "<h2>5. All Categories in Database</h2>";
try {
    $query = "SELECT * FROM event_categories WHERE user_id = :user_id ORDER BY category_name ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $allCategories = $stmt->fetchAll();
    
    if (empty($allCategories)) {
        echo "<p style='color: red;'>No categories found</p>";
        echo "<p><a href='create_categories.php'>Create Categories</a></p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Icon</th>";
        echo "<th>Name</th>";
        echo "<th>Color</th>";
        echo "<th>User ID</th>";
        echo "<th>Default</th>";
        echo "</tr>";
        
        foreach ($allCategories as $cat) {
            echo "<tr>";
            echo "<td>{$cat['category_id']}</td>";
            echo "<td style='font-size: 24px;'>{$cat['icon']}</td>";
            echo "<td><strong>{$cat['category_name']}</strong></td>";
            echo "<td>";
            echo "<div style='background: {$cat['color_hex']}; width: 50px; height: 20px; border-radius: 4px;'></div>";
            echo "<small>{$cat['color_hex']}</small>";
            echo "</td>";
            echo "<td>{$cat['user_id']}</td>";
            echo "<td>" . ($cat['is_default'] ? '✓' : '') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test del dropdown HTML
echo "<h2>6. Dropdown Test</h2>";
echo "<p>This is how the dropdown should look:</p>";
echo "<select>";
echo "<option value=''>Select work type...</option>";
foreach ($allCategories as $cat) {
    echo "<option value='{$cat['category_id']}'>{$cat['icon']} {$cat['category_name']}</option>";
}
echo "</select>";

echo "<hr>";
echo "<p><a href='index.php'>Back to Calendar</a></p>";
?>

<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        max-width: 900px;
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
    select {
        padding: 10px;
        font-size: 14px;
        min-width: 300px;
    }
</style>