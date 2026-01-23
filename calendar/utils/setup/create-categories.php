<?php
/**
 * ============================================================
 * CREATE DEFAULT CATEGORIES - SETUP UTILITY
 * Run this once to create default categories for a user
 * ============================================================
 */

require_once '../../config.php';
require_once '../../app/Controllers/CategoryController.php';

// Require authentication
requireAuth();

$userId = getCurrentUserId();
$currentUser = getCurrentUser();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Default Categories</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
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
        .info-box {
            background: #e0f2fe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background: #d1fae5;
            border-left-color: #10b981;
            color: #065f46;
        }
        .error {
            background: #fee2e2;
            border-left-color: #ef4444;
            color: #991b1b;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #3b82f6;
            color: white;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #2563eb;
        }
        .category-preview {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            text-align: center;
            line-height: 30px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>🎨 Create Default Categories</h1>
    
    <div class="info-box">
        <strong>User:</strong> <?= e($currentUser['full_name']) ?> (ID: <?= $userId ?>)
    </div>

    <?php
    // Check if already has categories
    $category = new Category();
    $existingCategories = $category->getAllByUser($userId);
    
    if (!empty($existingCategories)) {
        echo '<div class="info-box error">';
        echo '<strong>⚠️ Warning:</strong> You already have ' . count($existingCategories) . ' categories.';
        echo '<br>Creating defaults will add more categories.';
        echo '</div>';
    }
    
    // Create categories if requested
    if (isset($_GET['create']) && $_GET['create'] === 'yes') {
        echo '<h2>Creating Categories...</h2>';
        
        $controller = new CategoryController();
        
        // Capture output
        ob_start();
        $controller->createDefaults();
        $result = ob_get_clean();
        
        // Parse JSON result
        $data = json_decode($result, true);
        
        if ($data && $data['success']) {
            echo '<div class="info-box success">';
            echo '<strong>✅ Success!</strong><br>';
            echo $data['message'] . '<br>';
            echo 'Created: ' . count($data['created']) . ' categories';
            if (!empty($data['errors'])) {
                echo '<br>Errors: ' . count($data['errors']);
            }
            echo '</div>';
        } else {
            echo '<div class="info-box error">';
            echo '<strong>❌ Error:</strong> Failed to create categories';
            echo '</div>';
        }
    }
    ?>

    <?php if (!isset($_GET['create'])): ?>
        <h2>Default Categories to Create</h2>
        
        <table>
            <tr>
                <th>Icon</th>
                <th>Name</th>
                <th>Color</th>
                <th>Default</th>
            </tr>
            <tr>
                <td style="font-size: 24px;">📋</td>
                <td><strong>JWO</strong></td>
                <td><div class="category-preview" style="background: #3b82f6;"></div> #3b82f6</td>
                <td>✅ Yes</td>
            </tr>
            <tr>
                <td style="font-size: 24px;">📄</td>
                <td><strong>Contract</strong></td>
                <td><div class="category-preview" style="background: #10b981;"></div> #10b981</td>
                <td>No</td>
            </tr>
            <tr>
                <td style="font-size: 24px;">📊</td>
                <td><strong>Proposal</strong></td>
                <td><div class="category-preview" style="background: #f59e0b;"></div> #f59e0b</td>
                <td>No</td>
            </tr>
            <tr>
                <td style="font-size: 24px;">🔥</td>
                <td><strong>Hoodvent</strong></td>
                <td><div class="category-preview" style="background: #ef4444;"></div> #ef4444</td>
                <td>No</td>
            </tr>
            <tr>
                <td style="font-size: 24px;">🧹</td>
                <td><strong>Janitorial</strong></td>
                <td><div class="category-preview" style="background: #8b5cf6;"></div> #8b5cf6</td>
                <td>No</td>
            </tr>
            <tr>
                <td style="font-size: 24px;">🔧</td>
                <td><strong>Installation</strong></td>
                <td><div class="category-preview" style="background: #f97316;"></div> #f97316</td>
                <td>No</td>
            </tr>
            <tr>
                <td style="font-size: 24px;">🍳</td>
                <td><strong>Kitchen</strong></td>
                <td><div class="category-preview" style="background: #ec4899;"></div> #ec4899</td>
                <td>No</td>
            </tr>
            <tr>
                <td style="font-size: 24px;">👥</td>
                <td><strong>Staff</strong></td>
                <td><div class="category-preview" style="background: #14b8a6;"></div> #14b8a6</td>
                <td>No</td>
            </tr>
        </table>
        
        <a href="?create=yes" class="btn">✨ Create These Categories</a>
        <a href="../../index.php" class="btn" style="background: #6b7280;">← Back to Calendar</a>
    <?php else: ?>
        <a href="../../index.php" class="btn">← Back to Calendar</a>
    <?php endif; ?>

    <h2>Current Categories</h2>
    <?php
    $currentCategories = $category->getAllByUser($userId);
    
    if (empty($currentCategories)) {
        echo '<div class="info-box">No categories found.</div>';
    } else {
        echo '<table>';
        echo '<tr><th>Icon</th><th>Name</th><th>Color</th><th>Default</th></tr>';
        
        foreach ($currentCategories as $cat) {
            echo '<tr>';
            echo '<td style="font-size: 24px;">' . e($cat['icon']) . '</td>';
            echo '<td><strong>' . e($cat['category_name']) . '</strong></td>';
            echo '<td><div class="category-preview" style="background: ' . e($cat['color_hex']) . ';"></div> ' . e($cat['color_hex']) . '</td>';
            echo '<td>' . ($cat['is_default'] ? '✅ Yes' : 'No') . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
    ?>
</body>
</html>