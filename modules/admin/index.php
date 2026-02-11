<?php
/**
 * Admin Hub - Administrative Functions Dashboard
 * Only accessible to Admin (role_id = 1)
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Admin Panel';
$back_url   = '';
$back_label = 'Home';

$adminFunctions = [
    [
        'name'  => 'Gestión de Usuarios',
        'desc'  => 'Crear, editar y administrar usuarios del sistema',
        'icon'  => 'fas fa-users-cog',
        'url'   => url('/modules/admin/users/'),
        'color' => 'linear-gradient(135deg, #667eea, #764ba2)',
    ],
    [
        'name'  => 'Gestión de Roles',
        'desc'  => 'Crear, editar y asignar roles de acceso',
        'icon'  => 'fas fa-user-tag',
        'url'   => url('/modules/admin/roles/'),
        'color' => 'linear-gradient(135deg, #f093fb, #f5576c)',
    ],
    [
        'name'  => 'Gestión de Permisos',
        'desc'  => 'Administrar permisos granulares del sistema',
        'icon'  => 'fas fa-key',
        'url'   => url('/modules/admin/permissions/'),
        'color' => 'linear-gradient(135deg, #4facfe, #00f2fe)',
    ],
    [
        'name'  => 'Mi Perfil',
        'desc'  => 'Editar información personal, foto y contraseña',
        'icon'  => 'fas fa-user-edit',
        'url'   => url('/modules/admin/profile/'),
        'color' => 'linear-gradient(135deg, #43e97b, #38f9d7)',
    ],
    [
        'name'  => 'Task Tracking',
        'desc'  => 'Seguimiento de solicitudes y tareas internas',
        'icon'  => 'fas fa-tasks',
        'url'   => url('/service_confirmation/'),
        'color' => 'linear-gradient(135deg, #17a2b8, #138496)',
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
        }
        .content {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .content h2 {
            color: white;
            font-size: 1.6rem;
            margin-bottom: 30px;
            text-align: center;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 35px 30px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 15px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
        }
        .card h3 { font-size: 1.2rem; font-weight: 600; }
        .card p { color: #666; font-size: 0.9rem; line-height: 1.4; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="content">
        <h2>Funciones Administrativas</h2>
        <div class="cards">
            <?php foreach ($adminFunctions as $fn): ?>
                <a href="<?= $fn['url'] ?>" class="card">
                    <div class="card-icon" style="background: <?= $fn['color'] ?>">
                        <i class="<?= $fn['icon'] ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($fn['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($fn['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
