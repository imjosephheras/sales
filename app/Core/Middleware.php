<?php
/**
 * Middleware - Simple authentication guard
 *
 * Call at the top of any protected page to enforce login.
 * Equivalent to Laravel's 'auth' middleware.
 */

class Middleware
{
    /**
     * Require authentication. Redirects to login if not authenticated.
     *
     * Usage: Middleware::auth();
     */
    public static function auth(): void
    {
        if (!Auth::check()) {
            // Store intended URL so we can redirect after login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: /public/index.php?action=login');
            exit;
        }
    }

    /**
     * Redirect authenticated users away (for login page).
     * If user is already logged in, send them to dashboard.
     */
    public static function guest(): void
    {
        if (Auth::check()) {
            header('Location: /public/index.php?action=dashboard');
            exit;
        }
    }

    /**
     * Require a specific role_id.
     * Call after Middleware::auth().
     *
     * @param int|array $roles Single role_id or array of allowed role_ids
     */
    public static function role(int|array $roles): void
    {
        self::auth();

        $user = Auth::user();
        $allowed = is_array($roles) ? $roles : [$roles];

        if (!in_array((int) $user['role_id'], $allowed, true)) {
            http_response_code(403);
            die('403 Forbidden - You do not have permission to access this page.');
        }
    }
}
