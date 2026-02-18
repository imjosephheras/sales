<?php
/**
 * BILLING / ACCOUNTING MODULE
 * 3-column layout:
 * - Left: Pending documents ready to invoice
 * - Center: Active document (PDF viewer + actions)
 * - Right: Completed history
 */
require_once __DIR__ . '/../app/bootstrap.php';
Middleware::module('billing');

require_once 'config/db_config.php';

$current_user = $_SESSION['full_name'] ?? 'Admin';

// Dashboard layout variables
$page_title = 'Billing / Accounting';
$page_icon  = 'fas fa-file-invoice-dollar';
$page_slug  = 'billing';

$page_head = '<link rel="stylesheet" href="' . url('/billing/styles/billing.css') . '?v=' . time() . '">'
    . '<style>
    /* Adjust for dashboard content area */
    .dashboard-body .container {
        max-width: 100%;
        height: calc(100vh - 60px);
    }
    .dashboard-body .main-header {
        display: none;
    }
    .dashboard-body .db-main { padding: 0; }
    .dashboard-body .db-content { padding: 0; }
    </style>';

ob_start();
?>

    <!-- Main 3-Column Layout -->
    <div class="container">

        <!-- Column 1: Pending Documents -->
        <aside class="pending-panel">
            <?php include 'includes/pending_panel.php'; ?>
        </aside>

        <!-- Column 2: Active Document Viewer -->
        <main class="viewer-panel">
            <?php include 'includes/viewer_panel.php'; ?>
        </main>

        <!-- Column 3: Completed History -->
        <aside class="history-panel">
            <?php include 'includes/history_panel.php'; ?>
        </aside>

    </div>

    <script src="<?= url('/billing/js/billing.js') ?>?v=<?php echo time(); ?>"></script>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../app/Views/layouts/dashboard.php';
?>
