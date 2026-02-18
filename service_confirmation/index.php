<?php
/**
 * ADMIN PANEL (Module 10) - Task Tracking
 * 3-column layout:
 * - Left: All services with progress indicators
 * - Center: Task tracking checklist with multiple checkboxes
 * - Right: Complete history
 *
 * This panel shows ALL forms from ALL sellers.
 * Used to TRACK progress of all service requests internally.
 *
 * Purpose:
 * - See at a glance what has been done and what is pending
 * - Mark individual tasks as completed (site visit, quote sent, etc.)
 * - Keep internal notes for each service
 * - No need to ask sellers - just check the panel
 *
 * Note: Sellers confirm from their Request Form.
 * This panel is for internal management/tracking only.
 */
require_once __DIR__ . '/../app/bootstrap.php';
Middleware::module('admin_panel');

require_once 'config/db_config.php';

$page_title = 'Task Tracking';
$page_icon  = 'fas fa-tasks';
$page_slug  = 'admin_panel';

$page_head = '
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="' . url('/service_confirmation/styles/service_confirmation.css') . '">
    <style>
        .dashboard-body .container {
            height: calc(100vh - var(--topbar-height) - 48px);
        }
    </style>';

ob_start();
?>

    <!-- Main 3-Column Layout -->
    <div class="container">

        <!-- Column 1: Pending Services -->
        <aside class="pending-panel">
            <?php include 'includes/pending_panel.php'; ?>
        </aside>

        <!-- Column 2: Service Details -->
        <main class="detail-panel">
            <?php include 'includes/detail_panel.php'; ?>
        </main>

        <!-- Column 3: History -->
        <aside class="history-panel">
            <?php include 'includes/history_panel.php'; ?>
        </aside>

    </div>

    <script src="<?= url('/service_confirmation/js/service_confirmation.js') ?>"></script>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../app/Views/layouts/dashboard.php';
?>
