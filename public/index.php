<?php
/**
 * Public entry point - Routes auth actions
 *
 * All authentication requests go through here:
 *   ?action=login          → Show login form
 *   ?action=process_login  → Handle login POST
 *   ?action=logout         → Destroy session
 *   ?action=dashboard      → Protected dashboard (default when logged in)
 */

require_once __DIR__ . '/../app/bootstrap.php';

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'login':
        AuthController::showLogin();
        break;

    case 'process_login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /public/index.php?action=login');
            exit;
        }
        AuthController::processLogin();
        break;

    case 'logout':
        AuthController::logout();
        break;

    case 'dashboard':
    default:
        AuthController::dashboard();
        break;
}
