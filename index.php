<?php
/**
 * Main Dashboard - Home page (post-login)
 * Shows module quick-access cards inside the dashboard layout.
 */
require_once __DIR__ . '/app/bootstrap.php';
Middleware::auth();

$user    = Auth::user();
$modules = Gate::modules();

$page_title = 'Dashboard';
$page_icon  = 'fas fa-th-large';
$page_slug  = 'home';

// Gradient map per module slug for the cards
$gradientMap = [
    'contracts'   => 'linear-gradient(135deg, #a30000, #c70734)',
    'generator'   => 'linear-gradient(135deg, #c70734, #e53935)',
    'work_report' => 'linear-gradient(135deg, #001f54, #003080)',
    'reports'     => 'linear-gradient(135deg, #1a5f1a, #2d8a2d)',
    'billing'     => 'linear-gradient(135deg, #6f42c1, #8257d8)',
    'admin_panel' => 'linear-gradient(135deg, #17a2b8, #138496)',
    'calendar'    => 'linear-gradient(135deg, #e67e22, #d35400)',
];

// Font Awesome icon map
$iconMap = [
    'contracts'   => 'fas fa-file-contract',
    'generator'   => 'fas fa-file-signature',
    'work_report' => 'fas fa-clipboard-list',
    'reports'     => 'fas fa-chart-bar',
    'billing'     => 'fas fa-file-invoice-dollar',
    'admin_panel' => 'fas fa-cogs',
    'calendar'    => 'fas fa-calendar-alt',
];

// Short descriptions for each module
$descMap = [
    'contracts'   => 'Create and manage contract request forms',
    'generator'   => 'Generate contracts, proposals and quotes',
    'work_report' => 'Submit employee work reports',
    'reports'     => 'View data reports and analytics',
    'billing'     => 'Manage invoices and accounting',
    'admin_panel' => 'User, role and permission management',
    'calendar'    => 'Schedule and track events',
];

ob_start();
?>

<div class="db-home-welcome">
    <h2>Welcome, <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></h2>
    <p>Select a module to get started</p>
</div>

<div class="db-home-grid">
    <?php foreach ($modules as $mod): ?>
        <?php
            $slug     = $mod['slug'];
            $icon     = $iconMap[$slug] ?? 'fas fa-cube';
            $gradient = $gradientMap[$slug] ?? 'linear-gradient(135deg, #667eea, #764ba2)';
            $desc     = $descMap[$slug] ?? '';
        ?>
        <a href="<?= url('/' . ltrim($mod['url'], '/')) ?>" class="db-home-card">
            <div class="db-home-card-icon" style="background: <?= $gradient ?>">
                <i class="<?= $icon ?>"></i>
            </div>
            <div class="db-home-card-body">
                <h3><?= htmlspecialchars($mod['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/app/Views/layouts/dashboard.php';
?>
