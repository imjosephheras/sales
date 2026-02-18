<?php
/**
 * User Management - List all users
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Gestión de Usuarios';
$page_icon  = 'fas fa-users-cog';
$page_slug  = 'admin_panel';

// Determine if current user can delete (admin=1 or leader=2)
$canDelete = in_array((int)($_SESSION['role_id'] ?? 0), [1, 2], true);

// Handle delete (only admin/leader)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    Csrf::validateOrFail();
    if (!$canDelete) {
        http_response_code(403);
        die('403 Forbidden - Only Admin or Leader can delete.');
    }
    $userId = (int)($_POST['user_id'] ?? 0);
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId > 0 && $userId !== $currentUserId) {
        $pdo->prepare("DELETE FROM users WHERE user_id = :id")->execute([':id' => $userId]);
        $_SESSION['flash_success'] = 'Usuario eliminado correctamente.';
    }
    header('Location: ' . url('/modules/admin/users/'));
    exit;
}

$users = $pdo->query("
    SELECT u.user_id, u.username, u.email, u.full_name, u.created_at, u.role_id,
           r.name AS role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.role_id
    ORDER BY u.user_id ASC
")->fetchAll();

$success = $_SESSION['flash_success'] ?? null;
$error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$roleColors = [
    'admin' => '#dc3545', 'leader' => '#007bff', 'vendedor' => '#059669',
    'empleado' => '#6b7280', 'contabilidad' => '#7c3aed',
];

ob_start();
?>

<?php if ($success): ?>
    <div class="db-alert db-alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="db-alert db-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="db-page-header">
    <h2>Usuarios (<?= count($users) ?>)</h2>
    <a href="<?= url('/modules/admin/users/create.php') ?>" class="db-btn db-btn-success">
        <i class="fas fa-user-plus"></i> Crear Usuario
    </a>
</div>

<div class="db-table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Nombre</th>
                <th>Rol</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <?php
                    $roleSlug = strtolower($u['role_name'] ?? 'default');
                    $badgeColor = $roleColors[$roleSlug] ?? '#6b7280';
                ?>
                <tr>
                    <td><?= (int)$u['user_id'] ?></td>
                    <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="db-role-badge" style="background:<?= $badgeColor ?>"><?= htmlspecialchars($u['role_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <div class="db-actions">
                            <a href="<?= url('/modules/admin/users/edit.php?id=' . (int)$u['user_id']) ?>" class="db-btn db-btn-sm db-btn-edit"><i class="fas fa-edit"></i> Editar</a>
                            <?php if ((int)$u['user_id'] !== (int)($_SESSION['user_id'] ?? 0) && $canDelete): ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                    <button type="submit" class="db-btn db-btn-sm db-btn-delete"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
