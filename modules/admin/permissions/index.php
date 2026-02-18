<?php
/**
 * Permissions Management - List all permissions
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Gestion de Permisos';
$page_icon  = 'fas fa-key';
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
    $permId = (int)($_POST['permission_id'] ?? 0);
    if ($permId > 0) {
        $pdo->prepare("DELETE FROM permissions WHERE permission_id = :id")->execute([':id' => $permId]);
        Gate::clearCache();
        $_SESSION['flash_success'] = 'Permiso eliminado correctamente.';
    }
    header('Location: ' . url('/modules/admin/permissions/'));
    exit;
}

$permissions = $pdo->query("
    SELECT p.*,
           (SELECT COUNT(*) FROM role_permission rp WHERE rp.permission_id = p.permission_id) AS role_count
    FROM permissions p
    ORDER BY p.permission_id ASC
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
    <h2>Permisos (<?= count($permissions) ?>)</h2>
    <a href="<?= url('/modules/admin/permissions/create.php') ?>" class="btn-create">
        <i class="fas fa-plus"></i> Crear Permiso
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Clave (perm_key)</th>
                <th>Descripcion</th>
                <th>Roles</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permissions as $p): ?>
                <tr>
                    <td><?= (int)$p['permission_id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><code class="perm-key"><?= htmlspecialchars($p['perm_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge"><?= (int)$p['role_count'] ?></span></td>
                    <td>
                        <div class="actions">
                            <a href="<?= url('/modules/admin/permissions/edit.php?id=' . (int)$p['permission_id']) ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i> Editar</a>
                            <?php if ($canDelete): ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar este permiso?')">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="permission_id" value="<?= (int)$p['permission_id'] ?>">
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
