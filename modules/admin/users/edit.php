<?php
/**
 * Edit User
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Editar Usuario';
$back_url   = url('/modules/admin/users/');
$back_label = 'Usuarios';

$userId = (int)($_GET['id'] ?? 0);
if ($userId < 1) {
    header('Location: ' . url('/modules/admin/users/'));
    exit;
}

$stmt = $pdo->prepare("SELECT user_id, username, email, full_name, role_id FROM users WHERE user_id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['flash_error'] = 'Usuario no encontrado.';
    header('Location: ' . url('/modules/admin/users/'));
    exit;
}

$roles  = $pdo->query("SELECT role_id, name FROM roles ORDER BY role_id ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $roleId   = (int)($_POST['role_id'] ?? 0);
    $password = $_POST['password'] ?? '';

    if ($username === '')  $errors[] = 'El username es obligatorio.';
    if ($email === '')     $errors[] = 'El email es obligatorio.';
    if ($fullName === '')  $errors[] = 'El nombre completo es obligatorio.';
    if ($roleId < 1)      $errors[] = 'Selecciona un rol válido.';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El formato de email es inválido.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u AND user_id != :id");
        $check->execute([':u' => $username, ':id' => $userId]);
        if ((int)$check->fetchColumn() > 0) $errors[] = 'El username ya existe.';

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :e AND user_id != :id");
        $check->execute([':e' => $email, ':id' => $userId]);
        if ((int)$check->fetchColumn() > 0) $errors[] = 'El email ya existe.';
    }

    if (empty($errors)) {
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET username = :u, email = :e, full_name = :fn, role_id = :r, password_hash = :ph WHERE user_id = :id");
            $stmt->execute([
                ':u'  => $username, ':e' => $email, ':fn' => $fullName,
                ':r'  => $roleId, ':ph' => password_hash($password, PASSWORD_DEFAULT),
                ':id' => $userId,
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = :u, email = :e, full_name = :fn, role_id = :r WHERE user_id = :id");
            $stmt->execute([':u' => $username, ':e' => $email, ':fn' => $fullName, ':r' => $roleId, ':id' => $userId]);
        }

        Gate::clearCache();
        $_SESSION['flash_success'] = 'Usuario actualizado correctamente.';
        header('Location: ' . url('/modules/admin/users/'));
        exit;
    }

    $user['username']  = $username;
    $user['email']     = $email;
    $user['full_name'] = $fullName;
    $user['role_id']   = $roleId;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
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
        .form-group input, .form-group select {
            width: 100%; padding: 11px 14px; border: 2px solid #ddd; border-radius: 8px;
            font-size: 1rem; transition: border-color 0.2s; font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; }
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
            <h2><i class="fas fa-user-edit" style="color:#ffc107;"></i> Editar: <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h2>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
                    <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                           value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="full_name">Nombre Completo</label>
                    <input type="text" id="full_name" name="full_name" required
                           value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password"
                           placeholder="Dejar vacío para mantener la actual">
                    <div class="form-hint">Solo rellenar si deseas cambiar la contraseña.</div>
                </div>

                <div class="form-group">
                    <label for="role_id">Rol</label>
                    <select id="role_id" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int)$role['role_id'] ?>"
                                <?= ((int)$user['role_id'] === (int)$role['role_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Cambios</button>
                    <a href="<?= url('/modules/admin/users/') ?>" class="btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
