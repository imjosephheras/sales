<?php
/**
 * Edit Permission
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Editar Permiso';
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Permiso</title>
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
        .form-hint { font-size: 0.8rem; color: #888; margin-top: 4px; }
        .btn-row { display: flex; gap: 12px; margin-top: 25px; }
        .btn-submit {
            flex: 1; padding: 12px; background: #ffc107; color: #333; border: none;
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-submit:hover { background: #e0a800; }
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
            <h2><i class="fas fa-key" style="color:#ffc107;"></i> Editar Permiso</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
                    <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" required
                           value="<?= htmlspecialchars($perm['name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="perm_key">Clave (perm_key)</label>
                    <input type="text" id="perm_key" name="perm_key" required
                           value="<?= htmlspecialchars($perm['perm_key'], ENT_QUOTES, 'UTF-8') ?>"
                           pattern="[a-z][a-z0-9_]*">
                    <div class="form-hint">Solo minúsculas, números y guiones bajos.</div>
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($perm['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Cambios</button>
                    <a href="<?= url('/modules/admin/permissions/') ?>" class="btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
