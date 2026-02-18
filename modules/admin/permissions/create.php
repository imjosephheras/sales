<?php
/**
 * Create Permission
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Crear Permiso';
$page_icon  = 'fas fa-key';
$page_slug  = 'admin_panel';
$back_url   = url('/modules/admin/permissions/');
$back_label = 'Permisos';

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $old = [
        'name'        => trim($_POST['name'] ?? ''),
        'perm_key'    => trim($_POST['perm_key'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];

    if ($old['name'] === '')     $errors[] = 'El nombre es obligatorio.';
    if ($old['perm_key'] === '') $errors[] = 'La clave (perm_key) es obligatoria.';

    // Validate perm_key format: lowercase, underscores, no spaces
    if ($old['perm_key'] !== '' && !preg_match('/^[a-z][a-z0-9_]*$/', $old['perm_key'])) {
        $errors[] = 'La clave debe ser alfanumérica en minúsculas con guiones bajos (ej: manage_users).';
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM permissions WHERE perm_key = :key");
        $check->execute([':key' => $old['perm_key']]);
        if ((int)$check->fetchColumn() > 0) {
            $errors[] = 'Ya existe un permiso con esa clave.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO permissions (name, description, perm_key) VALUES (:name, :description, :perm_key)");
        $stmt->execute([
            ':name'        => $old['name'],
            ':description' => $old['description'],
            ':perm_key'    => $old['perm_key'],
        ]);

        Gate::clearCache();
        $_SESSION['flash_success'] = 'Permiso "' . $old['name'] . '" creado correctamente.';
        header('Location: ' . url('/modules/admin/permissions/'));
        exit;
    }
}

ob_start();
?>

<div class="db-form-card">
    <h2><i class="fas fa-key" style="color:#4facfe;"></i> Nuevo Permiso</h2>

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
                   value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: Manage Users">
        </div>

        <div class="db-form-group">
            <label for="perm_key">Clave (perm_key)</label>
            <input type="text" id="perm_key" name="perm_key" required
                   value="<?= htmlspecialchars($old['perm_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: manage_users" pattern="[a-z][a-z0-9_]*">
            <div class="db-form-hint">Solo minusculas, numeros y guiones bajos. Ej: manage_users</div>
        </div>

        <div class="db-form-group">
            <label for="description">Descripcion</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Descripcion del permiso"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit"><i class="fas fa-save"></i> Crear Permiso</button>
            <a href="<?= url('/modules/admin/permissions/') ?>" class="db-btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
