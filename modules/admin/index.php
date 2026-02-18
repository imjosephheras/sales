<?php
/**
 * Admin Hub - Administrative Functions Dashboard
 * Only accessible to Admin (role_id = 1)
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Admin Panel';
$page_icon  = 'fas fa-cogs';
$page_slug  = 'admin_panel';

$adminFunctions = [
    [
        'name'  => 'Gestión de Usuarios',
        'desc'  => 'Crear, editar y administrar usuarios del sistema',
        'icon'  => 'fas fa-users-cog',
        'url'   => url('/modules/admin/users/'),
        'color' => 'linear-gradient(135deg, #667eea, #764ba2)',
    ],
    [
        'name'  => 'Gestión de Roles',
        'desc'  => 'Crear, editar y asignar roles de acceso',
        'icon'  => 'fas fa-user-tag',
        'url'   => url('/modules/admin/roles/'),
        'color' => 'linear-gradient(135deg, #f093fb, #f5576c)',
    ],
    [
        'name'  => 'Gestión de Permisos',
        'desc'  => 'Administrar permisos granulares del sistema',
        'icon'  => 'fas fa-key',
        'url'   => url('/modules/admin/permissions/'),
        'color' => 'linear-gradient(135deg, #4facfe, #00f2fe)',
    ],
    [
        'name'  => 'Mi Perfil',
        'desc'  => 'Editar información personal, foto y contraseña',
        'icon'  => 'fas fa-user-edit',
        'url'   => url('/modules/admin/profile/'),
        'color' => 'linear-gradient(135deg, #43e97b, #38f9d7)',
    ],
    [
        'name'  => 'Task Tracking',
        'desc'  => 'Seguimiento de solicitudes y tareas internas',
        'icon'  => 'fas fa-tasks',
        'url'   => url('/service_confirmation/'),
        'color' => 'linear-gradient(135deg, #17a2b8, #138496)',
    ],
    [
        'name'  => 'Contract Status',
        'desc'  => 'Vista informativa del estado de contratos y contabilidad (solo lectura)',
        'icon'  => 'fas fa-eye',
        'url'   => url('/modules/admin/contract_status.php'),
        'color' => 'linear-gradient(135deg, #ff6f00, #ffca28)',
    ],
];

ob_start();
?>

<h2 style="font-size:1.3rem;color:#1f2937;font-weight:600;margin-bottom:24px;">Funciones Administrativas</h2>

<div class="db-admin-cards">
    <?php foreach ($adminFunctions as $fn): ?>
        <a href="<?= $fn['url'] ?>" class="db-admin-card">
            <div class="db-admin-card-icon" style="background: <?= $fn['color'] ?>">
                <i class="<?= $fn['icon'] ?>"></i>
            </div>
            <h3><?= htmlspecialchars($fn['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($fn['desc'], ENT_QUOTES, 'UTF-8') ?></p>
        </a>
    <?php endforeach; ?>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../app/Views/layouts/dashboard.php';
?>
