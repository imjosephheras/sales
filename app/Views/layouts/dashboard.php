<?php
/**
 * Dashboard Master Layout
 *
 * Usage pattern for each page:
 *
 *   <?php
 *   require_once __DIR__ . '/../app/bootstrap.php';
 *   Middleware::auth();
 *
 *   $page_title = 'Page Title';
 *   $page_icon  = 'fas fa-icon';       // optional
 *   $page_slug  = 'module_slug';       // for sidebar active state
 *   $page_head  = '';                  // optional extra <head> content
 *
 *   // ... page-specific PHP logic ...
 *
 *   ob_start();
 *   ?>
 *   <!-- Page HTML content here (no DOCTYPE/html/head/body) -->
 *   <?php
 *   $page_content = ob_get_clean();
 *   include __DIR__ . '/../app/Views/layouts/dashboard.php';
 *   ?>
 *
 * Variables consumed by this layout:
 *   $page_title   (string)  Page title
 *   $page_icon    (string)  Font Awesome icon class
 *   $page_slug    (string)  Active sidebar item slug
 *   $page_head    (string)  Extra HTML for <head> (stylesheets, etc.)
 *   $page_content (string)  Main page HTML content
 *   $page_scripts (string)  Extra HTML for end of <body> (scripts, etc.)
 *   $page_body_class (string) Extra CSS class for <body> (optional)
 */

$page_title      = $page_title ?? 'Dashboard';
$page_icon       = $page_icon ?? '';
$page_slug       = $page_slug ?? '';
$page_head       = $page_head ?? '';
$page_content    = $page_content ?? '';
$page_scripts    = $page_scripts ?? '';
$page_body_class = $page_body_class ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> - Sales Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= url('/assets/css/dashboard.css') ?>">
    <?= $page_head ?>
</head>
<body class="dashboard-body <?= htmlspecialchars($page_body_class, ENT_QUOTES, 'UTF-8') ?>">

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <?php include __DIR__ . '/../components/topbar.php'; ?>

    <main class="db-main">
        <div class="db-content">
            <?= $page_content ?>
        </div>
    </main>

    <div class="db-overlay" id="dbOverlay"></div>

    <script src="<?= url('/assets/js/dashboard.js') ?>"></script>
    <?= $page_scripts ?>
</body>
</html>
