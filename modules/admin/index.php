<?php
/**
 * Admin Hub - Administrative Functions Dashboard
 * Only accessible to Admin (role_id = 1)
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::role(1);

$current_user = $_SESSION['full_name'] ?? 'Admin';

$adminFunctions = [
    [
        'name'  => 'User Management',
        'desc'  => 'Create, view and manage system users',
        'icon'  => 'fas fa-users-cog',
        'url'   => url('/modules/admin/users.php'),
        'color' => 'linear-gradient(135deg, #667eea, #764ba2)',
    ],
    [
        'name'  => 'Task Tracking',
        'desc'  => 'Track service requests and internal tasks',
        'icon'  => 'fas fa-tasks',
        'url'   => url('/service_confirmation/'),
        'color' => 'linear-gradient(135deg, #17a2b8, #138496)',
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
        }
        .header {
            background: rgba(0,0,0,0.3);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .header-left a:hover { background: rgba(255,255,255,0.25); }
        .header-left h1 { font-size: 1.4rem; }
        .header-right { display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
        .content {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .content h2 {
            color: white;
            font-size: 1.6rem;
            margin-bottom: 30px;
            text-align: center;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 35px 30px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 15px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
        }
        .card h3 { font-size: 1.25rem; font-weight: 600; }
        .card p { color: #666; font-size: 0.95rem; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="<?= url('/') ?>"><i class="fas fa-home"></i> Home</a>
            <h1><i class="fas fa-cogs"></i> Admin Panel</h1>
        </div>
        <div class="header-right">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <div class="content">
        <h2>Administrative Functions</h2>
        <div class="cards">
            <?php foreach ($adminFunctions as $fn): ?>
                <a href="<?= $fn['url'] ?>" class="card">
                    <div class="card-icon" style="background: <?= $fn['color'] ?>">
                        <i class="<?= $fn['icon'] ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($fn['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($fn['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
