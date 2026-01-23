<?php
/**
 * ============================================================
 * TEST MODAL WITH CATEGORIES
 * Página de prueba para verificar el modal
 * ============================================================
 */

ob_start();
require_once '../../config.php';

if (!isLoggedIn()) {
    die('Please <a href="login.php">login</a> first');
}

$userId = getCurrentUserId();
$currentUser = getCurrentUser();

// Load categories
$categories = [];
try {
    $category = new Category();
    $categories = $category->getAllByUser($userId);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Dropdown Test</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
        }
        .info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
        }
        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .debug {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            margin: 20px 0;
            overflow-x: auto;
        }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #3b82f6;
            color: white;
        }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Category Dropdown Test</h1>
    
    <div class="info">
        <h2>Session Info</h2>
        <p><strong>User:</strong> <?= e($currentUser['full_name']) ?></p>
        <p><strong>User ID:</strong> <?= $userId ?></p>
    </div>
    
    <div class="info">
        <h2>Categories Loaded</h2>
        <div class="debug">
            <?php
            echo "Categories array:\n";
            echo "Count: " . count($categories) . "\n";
            echo "Type: " . gettype($categories) . "\n";
            echo "Is empty: " . (empty($categories) ? 'YES' : 'NO') . "\n";
            ?>
        </div>
        
        <?php if (count($categories) > 0): ?>
            <p class="success">✓ Categories loaded: <?= count($categories) ?></p>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Color</th>
                    <th>User ID</th>
                </tr>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $cat['category_id'] ?></td>
                    <td style="font-size: 20px;"><?= e($cat['icon']) ?></td>
                    <td><?= e($cat['category_name']) ?></td>
                    <td>
                        <div style="background: <?= e($cat['color_hex']) ?>; width: 40px; height: 20px; border-radius: 4px;"></div>
                    </td>
                    <td><?= $cat['user_id'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="error">✗ No categories loaded!</p>
        <?php endif; ?>
    </div>
    
    <div class="info">
        <h2>Dropdown Test #1 (Exact copy from index.php)</h2>
        <div class="form-group">
            <label for="eventCategory1">Work Type / Category *</label>
            <select id="eventCategory1" name="category_id" required>
                <option value="">Select work type...</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>">
                        <?= e($cat['icon']) ?> <?= e($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="debug">
            HTML generated:
            <pre><?= htmlspecialchars('<select id="eventCategory1" name="category_id" required>
    <option value="">Select work type...</option>
    <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat[\'category_id\'] ?>">
            <?= e($cat[\'icon\']) ?> <?= e($cat[\'category_name\']) ?>
        </option>
    <?php endforeach; ?>
</select>') ?></pre>
        </div>
    </div>
    
    <div class="info">
        <h2>Dropdown Test #2 (Direct loop)</h2>
        <div class="form-group">
            <label for="eventCategory2">Work Type / Category (Direct)</label>
            <select id="eventCategory2" name="category_id">
                <option value="">Select work type...</option>
                <?php
                foreach ($categories as $cat) {
                    echo "<option value=\"{$cat['category_id']}\">";
                    echo htmlspecialchars($cat['icon']) . " " . htmlspecialchars($cat['category_name']);
                    echo "</option>\n";
                }
                ?>
            </select>
        </div>
    </div>
    
    <div class="info">
        <h2>Raw Categories Array</h2>
        <div class="debug">
            <?php
            echo "var_dump(\$categories):\n\n";
            var_dump($categories);
            ?>
        </div>
    </div>
    
    <div class="info">
        <h2>View Page Source</h2>
        <p>Right-click this page and select "View Page Source" (Ctrl+U)</p>
        <p>Search for <code>&lt;select id="eventCategory1"</code></p>
        <p>You should see all the <code>&lt;option&gt;</code> tags with categories.</p>
    </div>
    
    <a href="index.php" class="back-link">← Back to Calendar</a>
    
    <script>
        // JavaScript test
        console.log('Categories in select #1:', document.getElementById('eventCategory1').options.length);
        console.log('Categories in select #2:', document.getElementById('eventCategory2').options.length);
        
        // Log each option
        const select = document.getElementById('eventCategory1');
        Array.from(select.options).forEach((option, index) => {
            console.log(`Option ${index}:`, option.value, option.text);
        });
    </script>
</body>
</html>