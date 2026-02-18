<?php
/**
 * Create Role
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Crear Rol';
$page_icon  = 'fas fa-user-tag';
$page_slug  = 'admin_panel';
$back_url   = url('/modules/admin/roles/');
$back_label = 'Roles';

// Load all permissions for checkbox assignment
$allPermissions = $pdo->query("SELECT * FROM permissions ORDER BY permission_id ASC")->fetchAll();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $old = [
        'name'        => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];
    $selectedPerms = array_map('intval', $_POST['permissions'] ?? []);

    if ($old['name'] === '') $errors[] = 'El nombre del rol es obligatorio.';

    // Check uniqueness
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = :name");
        $check->execute([':name' => $old['name']]);
        if ((int)$check->fetchColumn() > 0) {
            $errors[] = 'Ya existe un rol con ese nombre.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (:name, :description)");
        $stmt->execute([':name' => $old['name'], ':description' => $old['description']]);
        $newRoleId = (int)$pdo->lastInsertId();

        // Assign permissions
        if (!empty($selectedPerms)) {
            $ins = $pdo->prepare("INSERT INTO role_permission (role_id, permission_id) VALUES (:role_id, :perm_id)");
            foreach ($selectedPerms as $permId) {
                $ins->execute([':role_id' => $newRoleId, ':perm_id' => $permId]);
            }
        }

        Gate::clearCache();
        $_SESSION['flash_success'] = 'Rol "' . $old['name'] . '" creado correctamente.';
        header('Location: ' . url('/modules/admin/roles/'));
        exit;
    }
}

ob_start();
?>

<div class="db-form-card">
    <h2><i class="fas fa-user-tag" style="color:#f5576c;"></i> Nuevo Rol</h2>

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
                   value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: Supervisor">
        </div>

        <div class="db-form-group">
            <label for="description">Descripcion</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Descripcion breve del rol"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <?php if (!empty($allPermissions)): ?>
        <div class="db-form-group">
            <label>Permisos</label>
            <div class="db-checkbox-grid">
                <?php foreach ($allPermissions as $perm): ?>
                    <label class="db-checkbox-item">
                        <input type="checkbox" name="permissions[]" value="<?= (int)$perm['permission_id'] ?>"
                            <?= in_array((int)$perm['permission_id'], $selectedPerms ?? []) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($perm['name'], ENT_QUOTES, 'UTF-8') ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit"><i class="fas fa-save"></i> Crear Rol</button>
            <a href="<?= url('/modules/admin/roles/') ?>" class="db-btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
