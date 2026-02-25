<?php
/**
 * AuthController - Handles login, logout, and renders auth views
 */

class AuthController
{
    /**
     * Show login form
     */
    public static function showLogin(): void
    {
        Middleware::guest();

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        self::renderLoginForm($error);
    }

    /**
     * Process login POST request
     */
    public static function processLogin(): void
    {
        Csrf::validateOrFail();

        // ─── Rate limiting: max 5 attempts per IP in 15 minutes ──────
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = getDBConnection();

        // Purge old attempts (older than 15 minutes)
        $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->execute();

        // Count recent attempts from this IP
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = :ip AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $countStmt->execute([':ip' => $ip]);
        $attempts = (int) $countStmt->fetchColumn();

        if ($attempts >= 5) {
            $_SESSION['login_error'] = 'Too many login attempts. Please try again in 15 minutes.';
            header('Location: ' . url('/public/index.php?action=login'));
            exit;
        }

        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identity) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter your username/email and password.';
            header('Location: ' . url('/public/index.php?action=login'));
            exit;
        }

        $user = Auth::login($identity, $password);

        if (!$user) {
            // Record failed attempt
            $pdo->prepare("INSERT INTO login_attempts (ip_address) VALUES (:ip)")->execute([':ip' => $ip]);
            $_SESSION['login_error'] = 'Invalid credentials.';
            header('Location: ' . url('/public/index.php?action=login'));
            exit;
        }

        // Regenerate CSRF token after login
        Csrf::regenerate();

        // Check if user must change their default password
        $mustChange = $pdo->prepare("SELECT must_change_password FROM users WHERE user_id = :id");
        $mustChange->execute([':id' => $user['user_id']]);
        if ((int)$mustChange->fetchColumn() === 1) {
            header('Location: ' . url('/public/index.php?action=force_password_change'));
            exit;
        }

        // Redirect to intended URL or main dashboard (root index.php)
        $redirect = $_SESSION['intended_url'] ?? url('/');
        unset($_SESSION['intended_url']);

        // Validate redirect URL: must be a local path (prevent open redirect)
        $parsed = parse_url($redirect);
        if (
            !empty($parsed['host']) ||
            !empty($parsed['scheme']) ||
            !str_starts_with($redirect, '/')
        ) {
            $redirect = url('/');
        }

        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Process logout
     */
    public static function logout(): void
    {
        Auth::logout();
        header('Location: ' . url('/public/index.php?action=login'));
        exit;
    }

    /**
     * Show the forced password change form
     */
    public static function showForcePasswordChange(): void
    {
        Middleware::auth();

        $error = $_SESSION['password_change_error'] ?? null;
        unset($_SESSION['password_change_error']);
        self::renderForcePasswordChange($error);
    }

    /**
     * Process the forced password change
     */
    public static function processForcePasswordChange(): void
    {
        Middleware::auth();
        Csrf::validateOrFail();

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 8) {
            $_SESSION['password_change_error'] = 'Password must be at least 8 characters long.';
            header('Location: ' . url('/public/index.php?action=force_password_change'));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['password_change_error'] = 'Passwords do not match.';
            header('Location: ' . url('/public/index.php?action=force_password_change'));
            exit;
        }

        // Reject the default password
        if ($newPassword === 'admin123') {
            $_SESSION['password_change_error'] = 'You cannot reuse the default password.';
            header('Location: ' . url('/public/index.php?action=force_password_change'));
            exit;
        }

        $pdo = getDBConnection();
        $hash = Auth::hashPassword($newPassword);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash, must_change_password = 0 WHERE user_id = :id");
        $stmt->execute([':hash' => $hash, ':id' => Auth::id()]);

        header('Location: ' . url('/'));
        exit;
    }

    /**
     * Show protected dashboard
     */
    public static function dashboard(): void
    {
        Middleware::auth();

        $user = Auth::user();
        self::renderDashboard($user);
    }

    // ─── Views ────────────────────────────────────────────────

    private static function renderForcePasswordChange(?string $error): void
    {
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Sales Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .card h1 { text-align: center; color: #333; margin-bottom: 8px; font-size: 1.6rem; }
        .card .subtitle { text-align: center; color: #888; margin-bottom: 30px; font-size: 0.9rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #444; font-size: 0.9rem; }
        .form-group input {
            width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0;
            border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: #001f54; }
        .btn-submit {
            width: 100%; padding: 14px; background: linear-gradient(135deg, #001f54, #003080);
            color: white; border: none; border-radius: 10px; font-size: 1.1rem;
            font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,31,84,0.4); }
        .error-msg {
            background: #fee; color: #c00; padding: 10px 14px; border-radius: 8px;
            margin-bottom: 20px; font-size: 0.9rem; border-left: 4px solid #c00;
        }
        .warning {
            background: #fff3cd; color: #856404; padding: 12px 14px; border-radius: 8px;
            margin-bottom: 20px; font-size: 0.88rem; border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Change Password</h1>
        <p class="subtitle">Your account requires a password change before continuing.</p>

        <div class="warning">
            For security reasons, you must set a new password. It must be at least 8 characters long.
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/public/index.php?action=process_force_password_change') ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
            </div>

            <button type="submit" class="btn-submit">Update Password</button>
        </form>
    </div>
</body>
</html>
        <?php
    }

    private static function renderLoginForm(?string $error): void
    {
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sales Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: fadeIn 0.5s ease;
        }
        .login-card h1 {
            text-align: center;
            color: #333;
            margin-bottom: 8px;
            font-size: 1.8rem;
        }
        .login-card .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.2s;
            outline: none;
        }
        .form-group input:focus {
            border-color: #001f54;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #001f54, #003080);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,31,84,0.4);
        }
        .error-msg {
            background: #fee;
            color: #c00;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border-left: 4px solid #c00;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 150px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <img src="<?= url('/form_contract/Images/Facility.png') ?>" alt="Prime Facility Logo">
        </div>
        <h1>Sign In</h1>
        <p class="subtitle">Sales Management System</p>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/public/index.php?action=process_login') ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="identity">Username or Email</label>
                <input type="text" id="identity" name="identity" required autocomplete="username" autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
</body>
</html>
        <?php
    }

    private static function renderDashboard(array $user): void
    {
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sales Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: fadeIn 0.5s ease;
        }
        .user-info {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .user-info h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 6px;
        }
        .user-info p {
            color: #888;
            font-size: 0.95rem;
        }
        .buttons-container {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .btn {
            padding: 16px 32px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            color: white;
        }
        .btn-contract { background: linear-gradient(135deg, #a30000, #c70734); }
        .btn-sales    { background: linear-gradient(135deg, #001f54, #003080); }
        .btn-reports  { background: linear-gradient(135deg, #1a5f1a, #2d8a2d); }
        .btn-billing  { background: linear-gradient(135deg, #6f42c1, #8257d8); }
        .btn-confirm  { background: linear-gradient(135deg, #17a2b8, #138496); }
        .btn-calendar { background: linear-gradient(135deg, #e67e22, #d35400); }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .btn-logout {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 30px;
            background: #f5f5f5;
            color: #666;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: #e0e0e0; }
        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 0.85rem;
        }
        .logo-container {
            margin-bottom: 15px;
        }
        .logo-container img {
            max-width: 150px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="<?= url('/form_contract/Images/Facility.png') ?>" alt="Prime Facility Logo">
        </div>

        <div class="user-info">
            <h1>Welcome, <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Logged in as <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="buttons-container">
            <a href="<?= url('/form_contract/') ?>" class="btn btn-contract">Form for Contract</a>
            <a href="<?= url('/contract_generator/') ?>" class="btn btn-contract">Contract Generator</a>
            <a href="<?= url('/employee_work_report/') ?>" class="btn btn-sales">Employee Work Report</a>
            <a href="<?= url('/reports/') ?>" class="btn btn-reports">Reports</a>
            <a href="<?= url('/billing/') ?>" class="btn btn-billing">Billing / Accounting</a>
            <a href="<?= url('/service_confirmation/') ?>" class="btn btn-confirm">Admin Panel</a>
            <a href="<?= url('/calendar/') ?>" class="btn btn-calendar">Calendar</a>
        </div>

        <a href="<?= url('/public/index.php?action=logout') ?>" class="btn-logout">Sign Out</a>

        <div class="footer">
            &copy; <?= date('Y') ?> &mdash; Prime Facility Services Group
        </div>
    </div>
</body>
</html>
        <?php
    }
}
