<?php
/**
 * Edit User
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Editar Usuario';
$page_icon  = 'fas fa-user-edit';
$page_slug  = 'admin_panel';
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

ob_start();
?>

<div class="db-form-card">
    <h2><i class="fas fa-user-edit" style="color:#ffc107;"></i> Editar: <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="db-error-list">
            <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
            <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <?= Csrf::field() ?>

        <div class="db-form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required
                   value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="db-form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="db-form-group">
            <label for="full_name">Nombre Completo</label>
            <input type="text" id="full_name" name="full_name" required
                   value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="db-form-group">
            <label for="password">Nueva Contrase&ntilde;a</label>
            <input type="password" id="password" name="password"
                   placeholder="Dejar vacio para mantener la actual">
            <div class="db-form-hint">Solo rellenar si deseas cambiar la contrase&ntilde;a.</div>
        </div>

        <div class="db-form-group">
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

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit-warn"><i class="fas fa-save"></i> Guardar Cambios</button>
            <a href="<?= url('/modules/admin/users/') ?>" class="db-btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
