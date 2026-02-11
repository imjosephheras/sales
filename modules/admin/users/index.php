<?php
/**
 * User Management - List all users
 */
require_once __DIR__ . '/../../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Gestión de Usuarios';
$back_url   = url('/modules/admin/');
$back_label = 'Admin Panel';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    Csrf::validateOrFail();
    $userId = (int)($_POST['user_id'] ?? 0);
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId > 0 && $userId !== $currentUserId) {
        $pdo->prepare("DELETE FROM users WHERE user_id = :id")->execute([':id' => $userId]);
        $_SESSION['flash_success'] = 'Usuario eliminado correctamente.';
    }
    header('Location: ' . url('/modules/admin/users/'));
    exit;
}

$users = $pdo->query("
    SELECT u.user_id, u.username, u.email, u.full_name, u.created_at, u.role_id,
           r.name AS role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.role_id
    ORDER BY u.user_id ASC
")->fetchAll();

$success = $_SESSION['flash_success'] ?? null;
$error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$roleColors = [
    'admin' => '#dc3545', 'leader' => '#007bff', 'vendedor' => '#28a745',
    'empleado' => '#6c757d', 'contabilidad' => '#6f42c1',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
        }
        .content { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .toolbar h2 { color: white; font-size: 1.4rem; }
        .btn-create {
            display: inline-flex; align-items: center; gap: 8px;
            background: #28a745; color: white; text-decoration: none;
            padding: 10px 22px; border-radius: 8px; font-weight: 600;
            font-size: 0.95rem; transition: background 0.2s, transform 0.2s;
        }
        .btn-create:hover { background: #218838; transform: translateY(-2px); }
        .alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .table-wrapper { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th { padding: 14px 20px; text-align: left; font-size: 0.85rem; text-transform: uppercase; color: #666; font-weight: 600; letter-spacing: 0.5px; }
        td { padding: 14px 20px; border-top: 1px solid #eee; font-size: 0.95rem; color: #333; }
        tr:hover td { background: #f8f9fa; }
        .role-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: white; }
        .actions { display: flex; gap: 8px; }
        .btn-sm {
            padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 500;
            text-decoration: none; transition: background 0.2s; border: none; cursor: pointer; font-family: inherit;
        }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-edit:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="content">
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="toolbar">
            <h2>Usuarios (<?= count($users) ?>)</h2>
            <a href="<?= url('/modules/admin/users/create.php') ?>" class="btn-create">
                <i class="fas fa-user-plus"></i> Crear Usuario
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $roleSlug = strtolower($u['role_name'] ?? 'default');
                            $badgeColor = $roleColors[$roleSlug] ?? '#17a2b8';
                        ?>
                        <tr>
                            <td><?= (int)$u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="role-badge" style="background:<?= $badgeColor ?>"><?= htmlspecialchars($u['role_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= url('/modules/admin/users/edit.php?id=' . (int)$u['user_id']) ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i> Editar</a>
                                    <?php if ((int)$u['user_id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                            <?= Csrf::field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                            <button type="submit" class="btn-sm btn-delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
