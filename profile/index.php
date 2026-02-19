<?php
/**
 * User Profile - View and edit personal info, photo, and password
 *
 * Accessible to any authenticated user from the topbar avatar/name.
 * Uses the centralized FileStorageService for photo uploads and
 * Auth system for password verification.
 */
require_once __DIR__ . '/../app/bootstrap.php';
Middleware::auth();

$page_title = 'Mi Perfil';
$page_icon  = 'fas fa-user-circle';
$page_slug  = 'profile';

$userId = Auth::id();

// Fetch current user data from DB (including role name)
$stmt = $pdo->prepare("
    SELECT u.user_id, u.username, u.email, u.full_name, u.photo, u.created_at,
           r.name AS role_name
    FROM users u
    LEFT JOIN roles r ON r.role_id = u.role_id
    WHERE u.user_id = :id
");
$stmt->execute([':id' => $userId]);
$profile = $stmt->fetch();

if (!$profile) {
    die('User not found.');
}

$errors   = [];
$success  = null;
$activeTab = $_POST['form_action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $action = $_POST['form_action'] ?? 'profile';

    // ─── Update Personal Info + Photo ────────────────────────
    if ($action === 'profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if ($fullName === '') $errors[] = 'El nombre completo es obligatorio.';
        if ($email === '')    $errors[] = 'El email es obligatorio.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato de email es inválido.';
        }

        // Email uniqueness check
        if (empty($errors)) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :e AND user_id != :id");
            $check->execute([':e' => $email, ':id' => $userId]);
            if ((int)$check->fetchColumn() > 0) {
                $errors[] = 'El email ya está en uso por otro usuario.';
            }
        }

        // Photo upload via centralized FileStorageService
        $photoPath = $profile['photo'];
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $storage = new FileStorageService();
            $result  = $storage->uploadFile(
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

            // Update session data
            $_SESSION['full_name'] = $fullName;
            $_SESSION['email']     = $email;

            $profile['full_name'] = $fullName;
            $profile['email']     = $email;
            $profile['photo']     = $photoPath;

            $success = 'Perfil actualizado correctamente.';
        } else {
            // Preserve user input on error
            $profile['full_name'] = $fullName;
            $profile['email']     = $email;
        }

    // ─── Change Password ─────────────────────────────────────
    } elseif ($action === 'password') {
        $currentPw = $_POST['current_password'] ?? '';
        $newPw     = $_POST['new_password'] ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

        if ($currentPw === '') $errors[] = 'La contraseña actual es obligatoria.';
        if ($newPw === '')     $errors[] = 'La nueva contraseña es obligatoria.';
        if (strlen($newPw) < 6 && $newPw !== '') {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        }
        if ($newPw !== $confirmPw) $errors[] = 'Las contraseñas no coinciden.';

        // Verify current password against DB hash
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
            $stmt->execute([':ph' => Auth::hashPassword($newPw), ':id' => $userId]);
            $success = 'Contraseña actualizada correctamente.';
        }

    // ─── Delete Account ──────────────────────────────────────
    } elseif ($action === 'delete_account') {
        $confirmPw = $_POST['delete_password'] ?? '';

        if ($confirmPw === '') {
            $errors[] = 'Debes ingresar tu contraseña para confirmar la eliminación.';
        }

        if (empty($errors)) {
            $pwStmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = :id");
            $pwStmt->execute([':id' => $userId]);
            $hash = $pwStmt->fetchColumn();

            if (!password_verify($confirmPw, $hash)) {
                $errors[] = 'La contraseña es incorrecta.';
            }
        }

        if (empty($errors)) {
            // Delete profile photo if exists
            if ($profile['photo']) {
                $storage = new FileStorageService();
                $storage->deleteFile('uploads/profile_photos/' . $profile['photo']);
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);

            Auth::logout();
            header('Location: ' . url('/public/index.php?action=login'));
            exit;
        }
    }
}

// Resolve photo URL
$photoUrl = null;
if ($profile['photo']) {
    // Support both old path (modules/admin/uploads/photos/) and new centralized storage
    if (file_exists(__DIR__ . '/../modules/admin/uploads/photos/' . $profile['photo'])) {
        $photoUrl = url('/modules/admin/uploads/photos/' . $profile['photo']);
    } else {
        $photoUrl = url('/storage/uploads/profile_photos/' . $profile['photo']);
    }
}

// User initials for fallback avatar
$nameParts = explode(' ', trim($profile['full_name']));
$initials  = strtoupper(substr($nameParts[0], 0, 1));
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
}

// Member since date
$memberSince = '';
if (!empty($profile['created_at'])) {
    $memberSince = date('d M Y', strtotime($profile['created_at']));
}

$page_head = '<link rel="stylesheet" href="' . url('/profile/profile.css') . '">';

ob_start();
?>

<?php if ($success): ?>
    <div class="db-alert db-alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="db-error-list">
        <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
        <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="profile-layout">

    <!-- ─── Profile Header Card ────────────────────────────────── -->
    <div class="profile-header-card">
        <div class="profile-header-avatar">
            <?php if ($photoUrl): ?>
                <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
            <?php else: ?>
                <span class="profile-header-initials"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="profile-header-info">
            <h2 class="profile-header-name"><?= htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="profile-header-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="profile-header-meta">
                <span class="profile-role-badge"><i class="fas fa-shield-alt"></i> <?= htmlspecialchars($profile['role_name'] ?? 'Sin rol', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="profile-header-username"><i class="fas fa-at"></i> <?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($memberSince): ?>
                    <span class="profile-header-since"><i class="fas fa-calendar-alt"></i> Miembro desde <?= htmlspecialchars($memberSince, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="profile-cards-grid">

        <!-- ─── Personal Info Card ─────────────────────────────── -->
        <div class="db-profile-card profile-card-full">
            <h2><i class="fas fa-user-edit" style="color:#43e97b;"></i> Informacion Personal</h2>

            <form method="POST" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <input type="hidden" name="form_action" value="profile">

                <div class="profile-avatar-upload">
                    <div class="profile-avatar-preview" id="avatarPreview">
                        <?php if ($photoUrl): ?>
                            <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" id="avatarImg">
                        <?php else: ?>
                            <i class="fas fa-user" id="avatarIcon"></i>
                        <?php endif; ?>
                    </div>
                    <div class="profile-avatar-controls">
                        <label for="photo" class="profile-upload-btn">
                            <i class="fas fa-camera"></i> Cambiar foto
                        </label>
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">
                        <span class="db-form-hint">Max. 10 MB &middot; JPG, PNG, GIF o WEBP</span>
                    </div>
                </div>

                <div class="profile-form-row">
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
                </div>

                <div class="db-form-group">
                    <label>Username</label>
                    <input type="text" value="<?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?>" disabled
                           style="background:#f8f9fa;color:#666;cursor:not-allowed;">
                    <div class="db-form-hint">El username no se puede cambiar.</div>
                </div>

                <div class="db-btn-row">
                    <button type="submit" class="db-btn-submit"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>

        <!-- ─── Change Password Card ──────────────────────────── -->
        <div class="db-profile-card profile-card-full">
            <h2><i class="fas fa-lock" style="color:#f5576c;"></i> Cambiar Contrase&ntilde;a</h2>

            <form method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="form_action" value="password">

                <div class="db-form-group">
                    <label for="current_password">Contrase&ntilde;a Actual</label>
                    <input type="password" id="current_password" name="current_password" required
                           autocomplete="current-password">
                    <div class="db-form-hint">Ingresa tu contraseña actual para verificar tu identidad.</div>
                </div>

                <div class="profile-form-row">
                    <div class="db-form-group">
                        <label for="new_password">Nueva Contrase&ntilde;a</label>
                        <input type="password" id="new_password" name="new_password" required
                               placeholder="Min. 6 caracteres" autocomplete="new-password">
                    </div>
                    <div class="db-form-group">
                        <label for="confirm_password">Confirmar Nueva Contrase&ntilde;a</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               autocomplete="new-password">
                    </div>
                </div>

                <div class="db-btn-row">
                    <button type="submit" class="db-btn-submit" style="background:#001f54;">
                        <i class="fas fa-key"></i> Cambiar Contrase&ntilde;a
                    </button>
                </div>
            </form>
        </div>

        <!-- ─── Danger Zone - Delete Account Card ─────────────── -->
        <div class="db-profile-card profile-card-full profile-danger-card">
            <h2><i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i> Zona de Peligro</h2>
            <p class="profile-danger-text">
                Eliminar tu cuenta es una accion permanente e irreversible. Se eliminarán todos tus datos personales.
            </p>

            <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.');">
                <?= Csrf::field() ?>
                <input type="hidden" name="form_action" value="delete_account">

                <div class="db-form-group">
                    <label for="delete_password">Confirma tu contraseña para eliminar</label>
                    <input type="password" id="delete_password" name="delete_password" required
                           autocomplete="current-password"
                           placeholder="Ingresa tu contraseña">
                </div>

                <div class="db-btn-row">
                    <button type="submit" class="db-btn db-btn-delete">
                        <i class="fas fa-trash-alt"></i> Eliminar mi cuenta
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php
$page_scripts = <<<'SCRIPT'
<script>
// Live preview when selecting a new photo
document.getElementById('photo').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) return;

    // Validate MIME type on client side
    var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (allowed.indexOf(file.type) === -1) {
        alert('Tipo de archivo no permitido. Usa JPG, PNG, GIF o WEBP.');
        e.target.value = '';
        return;
    }

    // Validate size (10MB)
    if (file.size > 10 * 1024 * 1024) {
        alert('La imagen no puede pesar más de 10 MB.');
        e.target.value = '';
        return;
    }

    var reader = new FileReader();
    reader.onload = function(ev) {
        var preview = document.getElementById('avatarPreview');
        var icon = document.getElementById('avatarIcon');
        var img = document.getElementById('avatarImg');

        if (img) {
            img.src = ev.target.result;
        } else {
            if (icon) icon.style.display = 'none';
            var newImg = document.createElement('img');
            newImg.src = ev.target.result;
            newImg.alt = 'Avatar';
            newImg.id = 'avatarImg';
            preview.appendChild(newImg);
        }
    };
    reader.readAsDataURL(file);
});
</script>
SCRIPT;

$page_content = ob_get_clean();
include __DIR__ . '/../app/Views/layouts/dashboard.php';
?>
