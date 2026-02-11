<?php
/**
 * User Profile - Edit personal info, photo, and password
 * Accessible to any authenticated user (not just admin)
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::auth();

$page_title = 'Mi Perfil';
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
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowed, true)) {
                $errors[] = 'Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.';
            } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La imagen no puede superar 2 MB.';
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $dest = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                    // Delete old photo
                    if ($profile['photo'] && file_exists($uploadDir . $profile['photo'])) {
                        unlink($uploadDir . $profile['photo']);
                    }
                    $photoPath = $filename;
                } else {
                    $errors[] = 'Error al subir la imagen.';
                }
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
    $photoUrl = url('/modules/admin/uploads/photos/' . $profile['photo']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
        }
        .content { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .profile-card {
            background: white; border-radius: 16px; padding: 40px 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 25px;
        }
        .profile-card h2 { font-size: 1.3rem; margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px; }
        .avatar-section { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .avatar {
            width: 80px; height: 80px; border-radius: 50%; background: #e9ecef;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #999; overflow: hidden; flex-shrink: 0;
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-info { font-size: 0.9rem; color: #666; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #444; font-size: 0.9rem; }
        .form-group input {
            width: 100%; padding: 11px 14px; border: 2px solid #ddd; border-radius: 8px;
            font-size: 1rem; transition: border-color 0.2s; font-family: inherit;
        }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .form-hint { font-size: 0.8rem; color: #888; margin-top: 4px; }
        .btn-submit {
            padding: 12px 30px; background: #28a745; color: white; border: none;
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-submit:hover { background: #218838; }
        .btn-password {
            padding: 12px 30px; background: #007bff; color: white; border: none;
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-password:hover { background: #0069d9; }
        .section-divider { border: none; border-top: 2px solid #eee; margin: 30px 0; }
        .error-list { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; padding: 14px 20px; margin-bottom: 20px; }
        .error-list ul { margin: 5px 0 0 18px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="content">
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <strong><i class="fas fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
                <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <!-- Profile Info -->
        <div class="profile-card">
            <h2><i class="fas fa-user-edit" style="color:#43e97b;"></i> Información Personal</h2>

            <form method="POST" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <input type="hidden" name="form_action" value="profile">

                <div class="avatar-section">
                    <div class="avatar">
                        <?php if ($photoUrl): ?>
                            <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="avatar-info">
                        <div class="form-group" style="margin-bottom:0">
                            <label for="photo">Cambiar foto de perfil</label>
                            <input type="file" id="photo" name="photo" accept="image/*">
                            <div class="form-hint">Máx. 2 MB. JPG, PNG, GIF o WEBP.</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="full_name">Nombre Completo</label>
                    <input type="text" id="full_name" name="full_name" required
                           value="<?= htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?>" disabled
                           style="background:#f8f9fa;color:#666;">
                    <div class="form-hint">El username no se puede cambiar.</div>
                </div>

                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Perfil</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <h2><i class="fas fa-lock" style="color:#f5576c;"></i> Cambiar Contraseña</h2>

            <form method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="form_action" value="password">

                <div class="form-group">
                    <label for="current_password">Contraseña Actual</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" required
                           placeholder="Mín. 6 caracteres">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn-password"><i class="fas fa-key"></i> Cambiar Contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>
