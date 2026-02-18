<?php
/**
 * Edit Permission
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Editar Permiso';
$page_icon  = 'fas fa-key';
$page_slug  = 'admin_panel';
$back_url   = url('/modules/admin/permissions/');
$back_label = 'Permisos';

$permId = (int)($_GET['id'] ?? 0);
if ($permId < 1) {
    header('Location: ' . url('/modules/admin/permissions/'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM permissions WHERE permission_id = :id");
$stmt->execute([':id' => $permId]);
$perm = $stmt->fetch();

if (!$perm) {
    $_SESSION['flash_error'] = 'Permiso no encontrado.';
    header('Location: ' . url('/modules/admin/permissions/'));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $name        = trim($_POST['name'] ?? '');
    $permKey     = trim($_POST['perm_key'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '')    $errors[] = 'El nombre es obligatorio.';
    if ($permKey === '') $errors[] = 'La clave (perm_key) es obligatoria.';

    if ($permKey !== '' && !preg_match('/^[a-z][a-z0-9_]*$/', $permKey)) {
        $errors[] = 'La clave debe ser alfanumérica en minúsculas con guiones bajos.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM permissions WHERE perm_key = :key AND permission_id != :id");
        $check->execute([':key' => $permKey, ':id' => $permId]);
        if ((int)$check->fetchColumn() > 0) {
            $errors[] = 'Ya existe otro permiso con esa clave.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE permissions SET name = :name, description = :description, perm_key = :perm_key WHERE permission_id = :id");
        $stmt->execute([':name' => $name, ':description' => $description, ':perm_key' => $permKey, ':id' => $permId]);

        Gate::clearCache();
        $_SESSION['flash_success'] = 'Permiso actualizado correctamente.';
        header('Location: ' . url('/modules/admin/permissions/'));
        exit;
    }

    $perm['name']        = $name;
    $perm['perm_key']    = $permKey;
    $perm['description'] = $description;
}

ob_start();
?>

<div class="db-form-card">
    <h2><i class="fas fa-key" style="color:#ffc107;"></i> Editar Permiso</h2>

    <?php if (!empty($errors)): ?>
        <div class="db-error-list">
            <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
            <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <?= Csrf::field() ?>

        <div class="db-form-group">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" required
                   value="<?= htmlspecialchars($perm['name'], ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="db-form-group">
            <label for="perm_key">Clave (perm_key)</label>
            <input type="text" id="perm_key" name="perm_key" required
                   value="<?= htmlspecialchars($perm['perm_key'], ENT_QUOTES, 'UTF-8') ?>"
                   pattern="[a-z][a-z0-9_]*">
            <div class="db-form-hint">Solo minusculas, numeros y guiones bajos.</div>
        </div>

        <div class="db-form-group">
            <label for="description">Descripcion</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($perm['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit-warn"><i class="fas fa-save"></i> Guardar Cambios</button>
            <a href="<?= url('/modules/admin/permissions/') ?>" class="db-btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
