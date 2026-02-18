<?php
/**
 * User Profile - Edit personal info, photo, and password
 * Accessible to any authenticated user (not just admin)
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::auth();

$page_title = 'Mi Perfil';
$page_icon  = 'fas fa-user-circle';
$page_slug  = 'admin_panel';
$back_url   = url('/modules/admin/');
$back_label = 'Admin Panel';

$userId = (int)($_SESSION['user_id'] ?? 0);

// Fetch current user data from DB
$stmt = $pdo->prepare("SELECT user_id, username, email, full_name, photo FROM users WHERE user_id = :id");
$stmt->execute([':id' => $userId]);
$profile = $stmt->fetch();

if (!$profile) {
    die('User not found.');
}

$errors  = [];
$success = null;

$uploadDir = __DIR__ . '/../uploads/photos/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $action = $_POST['form_action'] ?? 'profile';

    if ($action === 'profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if ($fullName === '') $errors[] = 'El nombre completo es obligatorio.';
        if ($email === '')    $errors[] = 'El email es obligatorio.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato de email es inválido.';
        }

        // Uniqueness
        if (empty($errors)) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :e AND user_id != :id");
            $check->execute([':e' => $email, ':id' => $userId]);
            if ((int)$check->fetchColumn() > 0) $errors[] = 'El email ya está en uso por otro usuario.';
        }

        // Photo upload
        $photoPath = $profile['photo'];
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $storage = new FileStorageService();
            $result = $storage->uploadFile(
                $_FILES['photo'],
                'profile_photos',
                'images',
                'user_' . $userId
            );

            if ($result['success']) {
                // Delete old photo from storage
                if ($profile['photo']) {
                    $storage->deleteFile('uploads/profile_photos/' . $profile['photo']);
                }
                $photoPath = $result['filename'];
            } else {
                $errors[] = $result['error'];
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET full_name = :fn, email = :e, photo = :p WHERE user_id = :id");
            $stmt->execute([':fn' => $fullName, ':e' => $email, ':p' => $photoPath, ':id' => $userId]);

            $_SESSION['full_name'] = $fullName;
            $_SESSION['email']     = $email;

            $profile['full_name'] = $fullName;
            $profile['email']     = $email;
            $profile['photo']     = $photoPath;

            $success = 'Perfil actualizado correctamente.';
        } else {
            $profile['full_name'] = $fullName;
            $profile['email']     = $email;
        }

    } elseif ($action === 'password') {
        $currentPw = $_POST['current_password'] ?? '';
        $newPw     = $_POST['new_password'] ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

        if ($currentPw === '') $errors[] = 'La contraseña actual es obligatoria.';
        if ($newPw === '')     $errors[] = 'La nueva contraseña es obligatoria.';
        if (strlen($newPw) < 6 && $newPw !== '') $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        if ($newPw !== $confirmPw) $errors[] = 'Las contraseñas no coinciden.';

        // Verify current password
        if (empty($errors)) {
            $pwStmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = :id");
            $pwStmt->execute([':id' => $userId]);
            $hash = $pwStmt->fetchColumn();

            if (!password_verify($currentPw, $hash)) {
                $errors[] = 'La contraseña actual es incorrecta.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET password_hash = :ph WHERE user_id = :id");
            $stmt->execute([':ph' => password_hash($newPw, PASSWORD_DEFAULT), ':id' => $userId]);
            $success = 'Contraseña actualizada correctamente.';
        }
    }
}

$photoUrl = null;
if ($profile['photo']) {
    // Support both old path (modules/admin/uploads/photos/) and new centralized storage
    if (file_exists(__DIR__ . '/../uploads/photos/' . $profile['photo'])) {
        $photoUrl = url('/modules/admin/uploads/photos/' . $profile['photo']);
    } else {
        $photoUrl = url('/storage/uploads/profile_photos/' . $profile['photo']);
    }
}

ob_start();
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="db-error-list">
        <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
        <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<!-- Profile Info -->
<div class="db-profile-card">
    <h2><i class="fas fa-user-edit" style="color:#43e97b;"></i> Informacion Personal</h2>

    <form method="POST" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <input type="hidden" name="form_action" value="profile">

        <div class="db-avatar-section">
            <div class="db-avatar">
                <?php if ($photoUrl): ?>
                    <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="avatar-info">
                <div class="db-form-group" style="margin-bottom:0">
                    <label for="photo">Cambiar foto de perfil</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <div class="db-form-hint">Max. 2 MB. JPG, PNG, GIF o WEBP.</div>
                </div>
            </div>
        </div>

        <div class="db-form-group">
            <label for="full_name">Nombre Completo</label>
            <input type="text" id="full_name" name="full_name" required
                   value="<?= htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="db-form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="db-form-group">
            <label>Username</label>
            <input type="text" value="<?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?>" disabled
                   style="background:#f8f9fa;color:#666;">
            <div class="db-form-hint">El username no se puede cambiar.</div>
        </div>

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit"><i class="fas fa-save"></i> Guardar Perfil</button>
        </div>
    </form>
</div>

<!-- Change Password -->
<div class="db-profile-card">
    <h2><i class="fas fa-lock" style="color:#f5576c;"></i> Cambiar Contrase&ntilde;a</h2>

    <form method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="form_action" value="password">

        <div class="db-form-group">
            <label for="current_password">Contrase&ntilde;a Actual</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="db-form-group">
            <label for="new_password">Nueva Contrase&ntilde;a</label>
            <input type="password" id="new_password" name="new_password" required
                   placeholder="Min. 6 caracteres">
        </div>

        <div class="db-form-group">
            <label for="confirm_password">Confirmar Nueva Contrase&ntilde;a</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="db-btn-row">
            <button type="submit" class="db-btn-submit"><i class="fas fa-key"></i> Cambiar Contrase&ntilde;a</button>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../../app/Views/layouts/dashboard.php';
?>
