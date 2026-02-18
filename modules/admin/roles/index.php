<?php
/**
 * Roles Management - List all roles
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Gestion de Roles';
$page_icon  = 'fas fa-user-tag';
$page_slug  = 'admin_panel';
$back_url   = url('/modules/admin/');
$back_label = 'Admin Panel';

$canDelete = in_array((int)($_SESSION['role_id'] ?? 0), [1, 2], true);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!$canDelete) {
        http_response_code(403);
        die('403 Forbidden - Only Admin or Leader can delete.');
    }
    Csrf::validateOrFail();
    $roleId = (int)($_POST['role_id'] ?? 0);
    if ($roleId > 1) { // Never delete Admin role
        $pdo->prepare("DELETE FROM roles WHERE role_id = :id")->execute([':id' => $roleId]);
        Gate::clearCache();
        $_SESSION['flash_success'] = 'Rol eliminado correctamente.';
    }
    header('Location: ' . url('/modules/admin/roles/'));
    exit;
}

$roles = $pdo->query("
    SELECT r.*,
           (SELECT COUNT(*) FROM users u WHERE u.role_id = r.role_id) AS user_count
    FROM roles r
    ORDER BY r.role_id ASC
")->fetchAll();

$success = $_SESSION['flash_success'] ?? null;
$error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

ob_start();
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="toolbar">
    <h2>Roles (<?= count($roles) ?>)</h2>
    <a href="<?= url('/modules/admin/roles/create.php') ?>" class="btn-create">
        <i class="fas fa-plus"></i> Crear Rol
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Usuarios</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $r): ?>
                <tr>
                    <td><?= (int)$r['role_id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars($r['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge"><?= (int)$r['user_count'] ?></span></td>
                    <td>
                        <div class="actions">
                            <a href="<?= url('/modules/admin/roles/edit.php?id=' . (int)$r['role_id']) ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i> Editar</a>
                            <?php if ($canDelete && (int)$r['role_id'] !== 1): ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar este rol?')">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="role_id" value="<?= (int)$r['role_id'] ?>">
                                    <button type="submit" class="btn-sm btn-delete"><i class="fas fa-trash"></i></button>
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
