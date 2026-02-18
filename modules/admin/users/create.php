<?php
/**
 * Create User
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Crear Usuario';
$page_icon  = 'fas fa-user-plus';
$page_slug  = 'admin_panel';
$back_url   = url('/modules/admin/users/');
$back_label = 'Usuarios';

$roles  = $pdo->query("SELECT role_id, name FROM roles ORDER BY role_id ASC")->fetchAll();
$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $old = [
        'username'  => trim($_POST['username'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'role_id'   => (int)($_POST['role_id'] ?? 0),
    ];
    $password = $_POST['password'] ?? '';

    if ($old['username'] === '')  $errors[] = 'El username es obligatorio.';
    if ($old['email'] === '')     $errors[] = 'El email es obligatorio.';
    if ($old['full_name'] === '') $errors[] = 'El nombre completo es obligatorio.';
    if ($password === '')         $errors[] = 'La contraseña es obligatoria.';
    if ($old['role_id'] < 1)     $errors[] = 'Selecciona un rol válido.';

    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El formato de email es inválido.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
        $check->execute([':u' => $old['username']]);
        if ((int)$check->fetchColumn() > 0) $errors[] = 'El username ya existe.';

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :e");
        $check->execute([':e' => $old['email']]);
        if ((int)$check->fetchColumn() > 0) $errors[] = 'El email ya existe.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, full_name, password_hash, role_id)
            VALUES (:username, :email, :full_name, :password_hash, :role_id)
        ");
        $stmt->execute([
            ':username'      => $old['username'],
            ':email'         => $old['email'],
            ':full_name'     => $old['full_name'],
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role_id'       => $old['role_id'],
        ]);

        $_SESSION['flash_success'] = 'Usuario "' . $old['username'] . '" creado correctamente.';
        header('Location: ' . url('/modules/admin/users/'));
        exit;
    }
}

ob_start();
?>

<div class="db-form-card">
    <h2><i class="fas fa-user-plus" style="color:#667eea;"></i> Nuevo Usuario</h2>

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
                   value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: jdoe">
        </div>

        <div class="db-form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: jdoe@primefacility.com">
        </div>

        <div class="db-form-group">
            <label for="full_name">Nombre Completo</label>
            <input type="text" id="full_name" name="full_name" required
                   value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: John Doe">
        </div>

        <div class="db-form-group">
            <label for="password">Contrase&ntilde;a</label>
            <input type="password" id="password" name="password" required
                   placeholder="Min. 6 caracteres">
        </div>

        <div class="db-form-group">
            <label for="role_id">Rol</label>
            <select id="role_id" name="role_id" required>
                <option value="">-- Seleccionar rol --</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int)$role['role_id'] ?>"
                        <?= ((int)($old['role_id'] ?? 0) === (int)$role['role_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit"><i class="fas fa-save"></i> Crear Usuario</button>
            <a href="<?= url('/modules/admin/users/') ?>" class="db-btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
