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

        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identity) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter your username/email and password.';
            header('Location: ' . BASE_PATH . '/public/index.php?action=login');
            exit;
        }

        $user = Auth::login($identity, $password);

        if (!$user) {
            $_SESSION['login_error'] = 'Invalid credentials.';
            header('Location: ' . BASE_PATH . '/public/index.php?action=login');
            exit;
        }

        // Regenerate CSRF token after login
        Csrf::regenerate();

        // Redirect to intended URL or dashboard
        $redirect = $_SESSION['intended_url'] ?? (BASE_PATH . '/public/index.php?action=dashboard');
        unset($_SESSION['intended_url']);
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Process logout
     */
    public static function logout(): void
    {
        Auth::logout();
        header('Location: ' . BASE_PATH . '/public/index.php?action=login');
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
            <img src="<?= BASE_PATH ?>/form_contract/Images/Facility.png" alt="Prime Facility Logo">
        </div>
        <h1>Sign In</h1>
        <p class="subtitle">Sales Management System</p>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/public/index.php?action=process_login">
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
            <img src="<?= BASE_PATH ?>/form_contract/Images/Facility.png" alt="Prime Facility Logo">
        </div>

        <div class="user-info">
            <h1>Welcome, <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Logged in as <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="buttons-container">
            <a href="<?= BASE_PATH ?>/form_contract/" class="btn btn-contract">Form for Contract</a>
            <a href="<?= BASE_PATH ?>/contract_generator/contract_generator/" class="btn btn-contract">Contract Generator</a>
            <a href="<?= BASE_PATH ?>/employee_work_report/" class="btn btn-sales">Employee Work Report</a>
            <a href="<?= BASE_PATH ?>/reports/" class="btn btn-reports">Reports</a>
            <a href="<?= BASE_PATH ?>/billing/" class="btn btn-billing">Billing / Accounting</a>
            <a href="<?= BASE_PATH ?>/service_confirmation/" class="btn btn-confirm">Admin Panel</a>
            <a href="<?= BASE_PATH ?>/calendar/" class="btn btn-calendar">Calendar</a>
        </div>

        <a href="<?= BASE_PATH ?>/public/index.php?action=logout" class="btn-logout">Sign Out</a>

        <div class="footer">
            &copy; <?= date('Y') ?> &mdash; Prime Facility Services Group
        </div>
    </div>
</body>
</html>
        <?php
    }
}
