<?php
/**
 * Create Role
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Crear Rol';
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Rol</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
        }
        .content { max-width: 600px; margin: 40px auto; padding: 0 20px; }
        .form-card { background: white; border-radius: 16px; padding: 40px 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .form-card h2 { font-size: 1.4rem; margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px; }
        .error-list { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; padding: 14px 20px; margin-bottom: 20px; }
        .error-list ul { margin: 5px 0 0 18px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #444; font-size: 0.9rem; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 11px 14px; border: 2px solid #ddd; border-radius: 8px;
            font-size: 1rem; transition: border-color 0.2s; font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .checkbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #f8f9fa; border-radius: 6px; font-size: 0.9rem; }
        .checkbox-item input[type="checkbox"] { width: 16px; height: 16px; }
        .btn-row { display: flex; gap: 12px; margin-top: 25px; }
        .btn-submit {
            flex: 1; padding: 12px; background: #28a745; color: white; border: none;
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-submit:hover { background: #218838; }
        .btn-cancel {
            padding: 12px 24px; background: #6c757d; color: white; text-decoration: none;
            border-radius: 8px; font-size: 1rem; font-weight: 600; display: flex;
            align-items: center; justify-content: center; transition: background 0.2s;
        }
        .btn-cancel:hover { background: #5a6268; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="content">
        <div class="form-card">
            <h2><i class="fas fa-user-tag" style="color:#f5576c;"></i> Nuevo Rol</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
                    <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label for="name">Nombre del Rol</label>
                    <input type="text" id="name" name="name" required
                           value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ej: Supervisor">
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Descripción breve del rol"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <?php if (!empty($allPermissions)): ?>
                <div class="form-group">
                    <label>Permisos</label>
                    <div class="checkbox-grid">
                        <?php foreach ($allPermissions as $perm): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="permissions[]" value="<?= (int)$perm['permission_id'] ?>"
                                    <?= in_array((int)$perm['permission_id'], $selectedPerms ?? []) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($perm['name'], ENT_QUOTES, 'UTF-8') ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="btn-row">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Crear Rol</button>
                    <a href="<?= url('/modules/admin/roles/') ?>" class="btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
