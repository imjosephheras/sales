<?php
/**
 * ADMIN PANEL (Module 10)
 * 3-column layout:
 * - Left: Pending services (awaiting confirmation)
 * - Center: Service details viewer
 * - Right: Complete history
 *
 * This panel shows ALL forms from ALL sellers.
 * Used to manage and confirm all service requests.
 *
 * Workflow:
 * 1. Services appear in pending after contract is completed
 * 2. User marks service as Completed/Not Completed
 * 3. If Completed -> PDF generated, moved to billing "Ready to Invoice"
 * 4. All records remain in history (never deleted)
 */
require_once 'config/db_config.php';
session_start();

$current_user = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Module 10</title>
    <link rel="stylesheet" href="styles/service_confirmation.css">
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
                <i class="fas fa-cogs"></i>
                <h1>Admin Panel</h1>
            </div>
            <div class="user-section">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($current_user); ?></span>
            </div>
        </div>
    </header>

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

    <script src="js/service_confirmation.js"></script>
</body>
</html>
