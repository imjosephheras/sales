<?php
/**
 * Main index - Protected entry point
 * Redirects to auth system. All access requires login.
 */
require_once __DIR__ . '/app/bootstrap.php';
Middleware::auth();

$user = Auth::user();
$modules = Gate::modules();

$page_title = 'Main Menu';
$page_icon  = 'fas fa-home';
$back_url   = '';
$back_label = '';

// Style map per module slug (preserves original look & feel)
$styleMap = [
    'contracts'    => 'background: linear-gradient(135deg, #a30000, #c70734); box-shadow: 0 4px 15px rgba(163,0,0,0.35);',
    'generator'    => 'background: linear-gradient(135deg, #a30000, #c70734); box-shadow: 0 4px 15px rgba(163,0,0,0.35);',
    'work_report'  => 'background: linear-gradient(135deg, #001f54, #003080); box-shadow: 0 4px 15px rgba(0,31,84,0.35);',
    'reports'      => 'background: linear-gradient(135deg, #1a5f1a, #2d8a2d); box-shadow: 0 4px 15px rgba(26,95,26,0.35);',
    'billing'      => 'background: linear-gradient(135deg, #6f42c1, #8257d8); box-shadow: 0 4px 15px rgba(111,66,193,0.35);',
    'admin_panel'  => 'background: linear-gradient(135deg, #17a2b8, #138496); box-shadow: 0 4px 15px rgba(23,162,184,0.35);',
    'calendar'     => 'background: linear-gradient(135deg, #e67e22, #d35400); box-shadow: 0 4px 15px rgba(230,126,34,0.35);',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Menu - Sales & Form Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
        }

        .content {
            max-width: 600px;
            margin: 0 auto;
            padding: 50px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 0.5s ease;
        }

        .logo-container img {
            max-width: 180px;
            margin-bottom: 25px;
        }

        h1 {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
            text-align: center;
        }

        .subtitle {
            color: rgba(255,255,255,0.75);
            margin-bottom: 40px;
            font-size: 1.1rem;
            text-align: center;
        }

        .buttons-container {
            display: flex;
            flex-direction: column;
            gap: 22px;
            width: 100%;
        }

        .btn {
            padding: 18px 38px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.35);
        }

        .footer {
            margin-top: 40px;
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/modules/admin/includes/header.php'; ?>

    <div class="content">

        <div class="logo-container">
            <img src="<?= url('/form_contract/Images/Facility.png') ?>" alt="Prime Facility Logo">
        </div>

        <h1>Welcome, <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="subtitle">Select the application you want to access</p>

        <div class="buttons-container">
            <?php foreach ($modules as $mod): ?>
                <a href="<?= url('/' . ltrim($mod['url'], '/')) ?>"
                   class="btn"
                   style="<?= $styleMap[$mod['slug']] ?? 'background:#333;' ?>">
                    <span><?= $mod['icon'] ?></span>
                    <?= htmlspecialchars($mod['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="footer">
            &copy; <?= date('Y'); ?> — Prime Facility Services Group
        </div>

    </div>
</body>
</html>
