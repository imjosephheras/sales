<?php
/**
 * Sidebar Component
 *
 * Renders the dashboard sidebar with navigation links based on
 * the current user's role and module permissions.
 *
 * Expected variables (from the including page):
 *   $page_slug  (string)  Slug of the current module (for highlighting active link)
 *
 * Requires: Auth, Gate (loaded via bootstrap.php)
 */

$_sidebar_user    = Auth::user();
$_sidebar_modules = Gate::modules();
$_sidebar_name    = $_sidebar_user['full_name'] ?? 'User';
$_sidebar_role_id = (int)($_sidebar_user['role_id'] ?? 0);
$_sidebar_user_id = (int)($_sidebar_user['user_id'] ?? 0);

// Resolve role name from session or DB
$_sidebar_role_name = '';
$_sidebar_photo     = null;
if (isset($pdo)) {
    $_rStmt = $pdo->prepare("SELECT name FROM roles WHERE role_id = :rid LIMIT 1");
    $_rStmt->execute([':rid' => $_sidebar_role_id]);
    $_sidebar_role_name = $_rStmt->fetchColumn() ?: '';

    // Fetch user photo
    $_spStmt = $pdo->prepare("SELECT photo FROM users WHERE user_id = :uid LIMIT 1");
    $_spStmt->execute([':uid' => $_sidebar_user_id]);
    $_sidebar_photo_file = $_spStmt->fetchColumn() ?: null;
    if ($_sidebar_photo_file) {
        $_sidebar_photo = url('/storage/uploads/profile_photos/' . $_sidebar_photo_file);
    }
}

// User initials for avatar
$_nameParts = explode(' ', trim($_sidebar_name));
$_initials  = strtoupper(substr($_nameParts[0], 0, 1));
if (count($_nameParts) > 1) {
    $_initials .= strtoupper(substr(end($_nameParts), 0, 1));
}

// Font Awesome icon map per module slug
$_iconMap = [
    'contracts'   => 'fas fa-file-contract',
    'generator'   => 'fas fa-file-signature',
    'work_report' => 'fas fa-clipboard-list',
    'reports'     => 'fas fa-chart-bar',
    'billing'     => 'fas fa-file-invoice-dollar',
    'admin_panel' => 'fas fa-cogs',
    'calendar'    => 'fas fa-calendar-alt',
];

// Current page slug
$_current_slug = $page_slug ?? '';
?>

<aside class="db-sidebar" id="dbSidebar">
    <!-- Logo -->
    <div class="db-sidebar-logo">
        <img src="<?= url('/form_contract/Images/Facility.png') ?>" alt="Logo">
        <div class="db-sidebar-logo-text">
            Prime Facility
            <small>Services Group</small>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="db-sidebar-nav">
        <div class="db-sidebar-section-label">Menu</div>

        <!-- Dashboard Home -->
        <a href="<?= url('/') ?>"
           class="db-sidebar-link<?= ($_current_slug === 'home') ? ' active' : '' ?>"
           data-module="home">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>

        <div class="db-sidebar-divider"></div>
        <div class="db-sidebar-section-label">Modules</div>

        <?php foreach ($_sidebar_modules as $mod): ?>
            <?php
                $slug = $mod['slug'];
                $icon = $_iconMap[$slug] ?? 'fas fa-cube';
                $isActive = ($_current_slug === $slug);
                $href = url('/' . ltrim($mod['url'], '/'));
            ?>
            <a href="<?= $href ?>"
               class="db-sidebar-link<?= $isActive ? ' active' : '' ?>"
               data-module="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
                <i class="<?= $icon ?>"></i>
                <span><?= htmlspecialchars($mod['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Footer / User Info -->
    <div class="db-sidebar-footer">
        <div class="db-sidebar-user">
            <a href="<?= url('/profile/') ?>" class="db-sidebar-user-avatar" title="Mi Perfil" style="text-decoration:none;color:#fff;">
                <?php if ($_sidebar_photo): ?>
                    <img src="<?= htmlspecialchars($_sidebar_photo, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
                <?php else: ?>
                    <?= htmlspecialchars($_initials, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </a>
            <div class="db-sidebar-user-info">
                <a href="<?= url('/profile/') ?>" style="text-decoration:none;">
                    <div class="db-sidebar-user-name"><?= htmlspecialchars($_sidebar_name, ENT_QUOTES, 'UTF-8') ?></div>
                </a>
                <div class="db-sidebar-user-role"><?= htmlspecialchars($_sidebar_role_name, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <a href="<?= url('/public/index.php?action=logout') ?>" class="db-sidebar-logout" title="Cerrar Sesion">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>
