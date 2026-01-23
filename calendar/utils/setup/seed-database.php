<?php
/**
 * ============================================================
 * SEED DATABASE - SETUP UTILITY
 * Create test data for development
 * ⚠️ USE ONLY IN DEVELOPMENT
 * ============================================================
 */

require_once '../../config.php';

// Only allow in development
if (ENVIRONMENT !== 'development') {
    die('❌ This utility is only available in development mode');
}

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
    <title>Seed Database</title>
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
            border-bottom: 3px solid #f59e0b;
            padding-bottom: 10px;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #92400e;
        }
        .info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #065f46;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #f59e0b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px 10px 0;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #d97706;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🌱 Seed Database</h1>
    
    <div class="warning">
        <strong>⚠️ WARNING:</strong> This utility creates test data.<br>
        <strong>Environment:</strong> <?= ENVIRONMENT ?><br>
        <strong>User:</strong> <?= e($currentUser['full_name']) ?> (ID: <?= $userId ?>)
    </div>

    <?php
    if (isset($_GET['seed']) && $_GET['seed'] === 'yes') {
        echo '<h2>Seeding Database...</h2>';
        
        $created = [
            'categories' => 0,
            'events' => 0,
            'tasks' => 0
        ];
        
        try {
            // 1. Create categories if none exist
            $category = new Category();
            $existingCats = $category->getAllByUser($userId);
            
            if (empty($existingCats)) {
                echo '<p>Creating categories...</p>';
                
                $defaultCategories = [
                    ['name' => 'JWO', 'color' => '#3b82f6', 'icon' => '📋', 'default' => true],
                    ['name' => 'Contract', 'color' => '#10b981', 'icon' => '📄', 'default' => false],
                    ['name' => 'Proposal', 'color' => '#f59e0b', 'icon' => '📊', 'default' => false],
                    ['name' => 'Hoodvent', 'color' => '#ef4444', 'icon' => '🔥', 'default' => false],
                    ['name' => 'Janitorial', 'color' => '#8b5cf6', 'icon' => '🧹', 'default' => false],
                ];
                
                foreach ($defaultCategories as $cat) {
                    if ($category->create($userId, $cat['name'], $cat['color'], $cat['icon'], $cat['default'])) {
                        $created['categories']++;
                    }
                }
            } else {
                echo '<p>Categories already exist, skipping...</p>';
            }
            
            // 2. Create sample events
            $event = new Event();
            $categories = $category->getAllByUser($userId);
            
            if (!empty($categories)) {
                echo '<p>Creating sample events...</p>';
                
                $today = new Date();
                
                $sampleEvents = [
                    [
                        'title' => 'JWO-H100001142026-03-01',
                        'category_id' => $categories[0]['category_id'],
                        'start_date' => date('Y-m-d'),
                        'status' => 'confirmed'
                    ],
                    [
                        'title' => 'JWO-H100001152026-03-01',
                        'category_id' => $categories[0]['category_id'],
                        'start_date' => date('Y-m-d', strtotime('+3 days')),
                        'status' => 'pending'
                    ],
                    [
                        'title' => 'C-H100001162026-06-02',
                        'category_id' => $categories[1]['category_id'] ?? $categories[0]['category_id'],
                        'start_date' => date('Y-m-d', strtotime('+7 days')),
                        'status' => 'confirmed'
                    ],
                    [
                        'title' => 'P-H100001172026-12-01',
                        'category_id' => $categories[2]['category_id'] ?? $categories[0]['category_id'],
                        'start_date' => date('Y-m-d', strtotime('+14 days')),
                        'status' => 'pending'
                    ]
                ];
                
                foreach ($sampleEvents as $evt) {
                    $eventData = [
                        'user_id' => $userId,
                        'category_id' => $evt['category_id'],
                        'title' => $evt['title'],
                        'description' => 'Sample event created by seed script',
                        'location' => '123 Main Street, Houston TX',
                        'start_date' => $evt['start_date'],
                        'end_date' => $evt['start_date'],
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                        'is_all_day' => false,
                        'status' => $evt['status'],
                        'priority' => 'normal',
                        'is_recurring' => false
                    ];
                    
                    if ($event->create($eventData)) {
                        $created['events']++;
                    }
                }
            }
            
            // Success message
            echo '<div class="success">';
            echo '<strong>✅ Database seeded successfully!</strong><br>';
            echo 'Categories created: ' . $created['categories'] . '<br>';
            echo 'Events created: ' . $created['events'] . '<br>';
            echo 'Tasks created: ' . $created['tasks'];
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="warning">';
            echo '<strong>❌ Error:</strong> ' . e($e->getMessage());
            echo '</div>';
        }
        
        echo '<a href="../../index.php" class="btn">← Back to Calendar</a>';
        
    } else {
    ?>
        <h2>What will be created?</h2>
        
        <div class="info">
            <strong>📋 Categories:</strong> 5 default work categories<br>
            <strong>📅 Events:</strong> 4 sample events (this month and next)<br>
            <strong>✅ Tasks:</strong> 0 (optional)
        </div>
        
        <h3>Sample Events</h3>
        <pre>1. JWO-H100001142026-03-01 (Today, Confirmed)
2. JWO-H100001152026-03-01 (+3 days, Pending)
3. C-H100001162026-06-02 (+7 days, Confirmed)
4. P-H100001172026-12-01 (+14 days, Pending)</pre>
        
        <h3>Ready to seed?</h3>
        <a href="?seed=yes" class="btn btn-danger">🌱 Seed Database</a>
        <a href="../../index.php" class="btn">← Back to Calendar</a>
    <?php } ?>
</body>
</html>