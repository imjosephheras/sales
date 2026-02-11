<?php
/**
 * User Management - List all users
 * Only accessible to Admin (role_id = 1)
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::role(1);

$current_user = $_SESSION['full_name'] ?? 'Admin';

// Fetch all users with their role name
$stmt = $pdo->query("
    SELECT u.user_id, u.username, u.email, u.full_name, u.created_at,
           r.name AS role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.role_id
    ORDER BY u.user_id ASC
");
$users = $stmt->fetchAll();

// Flash message from create_user
$success = $_SESSION['flash_success'] ?? null;
$error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
        }
        .header {
            background: rgba(0,0,0,0.3);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .header-left a:hover { background: rgba(255,255,255,0.25); }
        .header-left h1 { font-size: 1.4rem; }
        .header-right { display: flex; align-items: center; gap: 8px; font-size: 0.95rem; color: white; }
        .content {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .toolbar h2 { color: white; font-size: 1.4rem; }
        .btn-create {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #28a745;
            color: white;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-create:hover { background: #218838; transform: translateY(-2px); }
        .alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .table-wrapper {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th {
            padding: 14px 20px;
            text-align: left;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        td {
            padding: 14px 20px;
            border-top: 1px solid #eee;
            font-size: 0.95rem;
            color: #333;
        }
        tr:hover td { background: #f8f9fa; }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
        }
        .role-admin        { background: #dc3545; }
        .role-leader       { background: #007bff; }
        .role-vendedor     { background: #28a745; }
        .role-empleado     { background: #6c757d; }
        .role-contabilidad { background: #6f42c1; }
        .role-default      { background: #17a2b8; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="<?= url('/modules/admin/') ?>"><i class="fas fa-arrow-left"></i> Admin Panel</a>
            <h1><i class="fas fa-users-cog"></i> User Management</h1>
        </div>
        <div class="header-right">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <div class="content">
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="toolbar">
            <h2>All Users (<?= count($users) ?>)</h2>
            <a href="<?= url('/modules/admin/create_user.php') ?>" class="btn-create">
                <i class="fas fa-user-plus"></i> Create User
            </a>
        </div>

        <div class="table-wrapper">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-users" style="font-size:2rem;margin-bottom:10px;"></i>
                    <p>No users found.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php
                                $roleSlug = strtolower($u['role_name'] ?? 'default');
                                $badgeClass = "role-{$roleSlug}";
                                if (!in_array($roleSlug, ['admin','leader','vendedor','empleado','contabilidad'])) {
                                    $badgeClass = 'role-default';
                                }
                            ?>
                            <tr>
                                <td><?= (int)$u['user_id'] ?></td>
                                <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="role-badge <?= $badgeClass ?>"><?= htmlspecialchars($u['role_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
