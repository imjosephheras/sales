<?php
/**
 * Topbar Component
 *
 * Renders the top navigation bar with page title, hamburger menu,
 * and user info (name, role, logout) on the right side.
 *
 * Expected variables (from the including page):
 *   $page_title  (string)  Title of the current page
 *   $page_icon   (string)  Font Awesome class for the page icon (optional)
 *
 * Requires: Auth (loaded via bootstrap.php), $pdo (from database config)
 */

$_topbar_title = $page_title ?? 'Dashboard';
$_topbar_icon  = $page_icon ?? '';

// User data for topbar
$_topbar_user    = Auth::user();
$_topbar_name    = $_topbar_user['full_name'] ?? 'User';
$_topbar_role_id = (int)($_topbar_user['role_id'] ?? 0);

// Resolve role name from DB
$_topbar_role_name = '';
if (isset($pdo)) {
    $_trStmt = $pdo->prepare("SELECT name FROM roles WHERE role_id = :rid LIMIT 1");
    $_trStmt->execute([':rid' => $_topbar_role_id]);
    $_topbar_role_name = $_trStmt->fetchColumn() ?: '';
}

// User initials for avatar
$_topbar_name_parts = explode(' ', trim($_topbar_name));
$_topbar_initials   = strtoupper(substr($_topbar_name_parts[0], 0, 1));
if (count($_topbar_name_parts) > 1) {
    $_topbar_initials .= strtoupper(substr(end($_topbar_name_parts), 0, 1));
}
?>

<header class="db-topbar" id="dbTopbar">
    <div class="db-topbar-left">
        <button class="db-topbar-hamburger" id="dbHamburger" type="button" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="db-topbar-title">
            <?php if ($_topbar_icon): ?>
                <i class="<?= htmlspecialchars($_topbar_icon, ENT_QUOTES, 'UTF-8') ?>"></i>
            <?php endif; ?>
            <?= htmlspecialchars($_topbar_title, ENT_QUOTES, 'UTF-8') ?>
        </h1>
    </div>
    <div class="db-topbar-right">
        <span class="db-topbar-date"><?= date('M d, Y') ?></span>
        <div class="db-topbar-user">
            <div class="db-topbar-user-avatar">
                <?= htmlspecialchars($_topbar_initials, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="db-topbar-user-info">
                <span class="db-topbar-user-name"><?= htmlspecialchars($_topbar_name, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="db-topbar-user-role"><?= htmlspecialchars($_topbar_role_name, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <a href="<?= url('/public/index.php?action=logout') ?>" class="db-topbar-logout" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>
