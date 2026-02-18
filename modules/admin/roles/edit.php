<?php
/**
 * Edit Role
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Editar Rol';
$page_icon  = 'fas fa-user-tag';
$page_slug  = 'admin_panel';
$back_url   = url('/modules/admin/roles/');
$back_label = 'Roles';

$roleId = (int)($_GET['id'] ?? 0);
if ($roleId < 1) {
    header('Location: ' . url('/modules/admin/roles/'));
    exit;
}

// Fetch role
$stmt = $pdo->prepare("SELECT * FROM roles WHERE role_id = :id");
$stmt->execute([':id' => $roleId]);
$role = $stmt->fetch();

if (!$role) {
    $_SESSION['flash_error'] = 'Rol no encontrado.';
    header('Location: ' . url('/modules/admin/roles/'));
    exit;
}

// All permissions
$allPermissions = $pdo->query("SELECT * FROM permissions ORDER BY permission_id ASC")->fetchAll();

// Currently assigned permissions
$assigned = $pdo->prepare("SELECT permission_id FROM role_permission WHERE role_id = :id");
$assigned->execute([':id' => $roleId]);
$assignedIds = $assigned->fetchAll(PDO::FETCH_COLUMN);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $selectedPerms = array_map('intval', $_POST['permissions'] ?? []);

    if ($name === '') $errors[] = 'El nombre del rol es obligatorio.';

    // Check uniqueness (excluding current)
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = :name AND role_id != :id");
        $check->execute([':name' => $name, ':id' => $roleId]);
        if ((int)$check->fetchColumn() > 0) {
            $errors[] = 'Ya existe otro rol con ese nombre.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE roles SET name = :name, description = :description WHERE role_id = :id");
        $stmt->execute([':name' => $name, ':description' => $description, ':id' => $roleId]);

        // Rebuild permissions
        $pdo->prepare("DELETE FROM role_permission WHERE role_id = :id")->execute([':id' => $roleId]);
        if (!empty($selectedPerms)) {
            $ins = $pdo->prepare("INSERT INTO role_permission (role_id, permission_id) VALUES (:role_id, :perm_id)");
            foreach ($selectedPerms as $permId) {
                $ins->execute([':role_id' => $roleId, ':perm_id' => $permId]);
            }
        }

        Gate::clearCache();
        $_SESSION['flash_success'] = 'Rol actualizado correctamente.';
        header('Location: ' . url('/modules/admin/roles/'));
        exit;
    }

    // Keep form values on error
    $role['name']        = $name;
    $role['description'] = $description;
    $assignedIds         = $selectedPerms;
}

ob_start();
?>

<div class="db-form-card">
    <h2><i class="fas fa-user-tag" style="color:#ffc107;"></i> Editar Rol: <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="db-error-list">
            <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
            <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <?= Csrf::field() ?>

        <div class="db-form-group">
            <label for="name">Nombre del Rol</label>
            <input type="text" id="name" name="name" required
                   value="<?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="db-form-group">
            <label for="description">Descripcion</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($role['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <?php if (!empty($allPermissions)): ?>
        <div class="db-form-group">
            <label>Permisos</label>
            <div class="db-checkbox-grid">
                <?php foreach ($allPermissions as $perm): ?>
                    <label class="db-checkbox-item">
                        <input type="checkbox" name="permissions[]" value="<?= (int)$perm['permission_id'] ?>"
                            <?= in_array((int)$perm['permission_id'], $assignedIds) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($perm['name'], ENT_QUOTES, 'UTF-8') ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit-warn"><i class="fas fa-save"></i> Guardar Cambios</button>
            <a href="<?= url('/modules/admin/roles/') ?>" class="db-btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
