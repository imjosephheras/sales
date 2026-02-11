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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing / Accounting</title>
    <link rel="stylesheet" href="styles/billing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <a href="../index.php" class="home-btn" title="Back to Home">
                    <i class="fas fa-home"></i> Home
                </a>
                <i class="fas fa-file-invoice-dollar"></i>
                <h1>Billing / Accounting</h1>
            </div>
            <div class="user-section">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($current_user); ?></span>
            </div>
        </div>
    </header>

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

    <script src="js/billing.js"></script>
</body>
</html>
