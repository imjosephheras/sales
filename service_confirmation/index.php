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

$current_user = $_SESSION['full_name'] ?? 'Admin';
$isAdmin      = ((int)($_SESSION['role_id'] ?? 0)) === 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Module 10</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/service_confirmation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <a href="<?= url('/') ?>" class="home-btn" title="Back to Home">
                    <i class="fas fa-home"></i> Home
                </a>
                <i class="fas fa-cogs"></i>
                <h1>Admin Panel</h1>
            </div>
            <?php if ($isAdmin): ?>
            <div class="adm-dropdown" id="adminDropdown">
                <button class="adm-dropdown-btn" type="button" onclick="this.parentElement.classList.toggle('open')">
                    <i class="fas fa-user-shield"></i>
                    <span><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
                    <i class="fas fa-chevron-down chevron"></i>
                </button>
                <div class="adm-dropdown-menu">
                    <a href="<?= url('/modules/admin/users/') ?>"><i class="fas fa-users-cog"></i> Gestión de Usuarios</a>
                    <a href="<?= url('/modules/admin/roles/') ?>"><i class="fas fa-user-tag"></i> Gestión de Roles</a>
                    <a href="<?= url('/modules/admin/permissions/') ?>"><i class="fas fa-key"></i> Gestión de Permisos</a>
                    <div class="adm-dropdown-divider"></div>
                    <a href="<?= url('/modules/admin/profile/') ?>"><i class="fas fa-user-edit"></i> Mi Perfil</a>
                    <a href="<?= url('/public/index.php?action=logout') ?>" class="logout-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
            <?php else: ?>
            <div class="user-section">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>
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
    <script>
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('adminDropdown');
        if (dd && !dd.contains(e.target)) {
            dd.classList.remove('open');
        }
    });
    </script>
</body>
</html>
