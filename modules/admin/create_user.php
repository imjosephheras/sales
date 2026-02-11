<?php
/**
 * Create User - Form and processing
 * Only accessible to Admin (role_id = 1)
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::role(1);

$current_user = $_SESSION['full_name'] ?? 'Admin';

// Load roles for the select dropdown
$roles = $pdo->query("SELECT role_id, name FROM roles ORDER BY role_id ASC")->fetchAll();

$errors  = [];
$old     = [];

// ── Process form submission ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateOrFail();

    $old = [
        'username'  => trim($_POST['username'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'role_id'   => (int)($_POST['role_id'] ?? 0),
    ];
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if ($old['username'] === '') $errors[] = 'Username is required.';
    if ($old['email'] === '')    $errors[] = 'Email is required.';
    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if ($password === '')        $errors[] = 'Password is required.';
    if ($old['role_id'] < 1)    $errors[] = 'Select a valid role.';

    // Validate email format
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email format is invalid.';
    }

    // Check uniqueness
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $check->execute([':username' => $old['username']]);
        if ((int)$check->fetchColumn() > 0) {
            $errors[] = 'Username already exists.';
        }

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $check->execute([':email' => $old['email']]);
        if ((int)$check->fetchColumn() > 0) {
            $errors[] = 'Email already exists.';
        }
    }

    // Insert user
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

        $_SESSION['flash_success'] = 'User "' . $old['username'] . '" created successfully.';
        header('Location: ' . url('/modules/admin/users.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
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
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-card h2 {
            font-size: 1.4rem;
            margin-bottom: 25px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error-list {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 14px 20px;
            margin-bottom: 20px;
        }
        .error-list ul { margin: 5px 0 0 18px; }
        .error-list li { margin-bottom: 4px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
            font-size: 0.9rem;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }
        .btn-submit {
            flex: 1;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-submit:hover { background: #218838; }
        .btn-cancel {
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .btn-cancel:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="<?= url('/modules/admin/users.php') ?>"><i class="fas fa-arrow-left"></i> Users</a>
            <h1><i class="fas fa-user-plus"></i> Create User</h1>
        </div>
        <div class="header-right">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <div class="content">
        <div class="form-card">
            <h2><i class="fas fa-user-plus" style="color:#667eea;"></i> New User</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <strong><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                           value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. jdoe">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. jdoe@primefacility.com">
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required
                           value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. John Doe">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Min. 6 characters">
                </div>

                <div class="form-group">
                    <label for="role_id">Role</label>
                    <select id="role_id" name="role_id" required>
                        <option value="">-- Select role --</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int)$role['role_id'] ?>"
                                <?= ((int)($old['role_id'] ?? 0) === (int)$role['role_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Create User
                    </button>
                    <a href="<?= url('/modules/admin/users.php') ?>" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
