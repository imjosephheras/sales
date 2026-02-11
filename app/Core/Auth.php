<?php
/**
 * Auth - Centralized authentication manager
 *
 * Provides Laravel-equivalent security:
 * - password_hash / password_verify / password_needs_rehash
 * - Session regeneration after login (prevents session fixation)
 * - Timing-attack safe password verification
 */

class Auth
{
    private static ?PDO $pdo = null;

    /**
     * Initialize with database connection
     */
    public static function init(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * Attempt login with username/email and password
     *
     * @return array|false User data on success, false on failure
     */
    public static function login(string $identity, string $password): array|false
    {
        if (!self::$pdo) {
            throw new RuntimeException('Auth not initialized. Call Auth::init($pdo) first.');
        }

        // Allow login with username OR email
        $stmt = self::$pdo->prepare(
            'SELECT user_id, username, email, password_hash, full_name, timezone, role_id
             FROM users
             WHERE username = :identity OR email = :identity
             LIMIT 1'
        );
        $stmt->execute([':identity' => $identity]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // IMPORTANT: Always run password_verify even if user not found
        // This prevents timing attacks that could reveal valid usernames
        $hash = $user['password_hash'] ?? '$2y$10$dummyhashtopreventtimingattackpadding000000';
        $valid = password_verify($password, $hash);

        if (!$user || !$valid) {
            return false;
        }

        // Rehash if PHP has upgraded the default algorithm/cost
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = self::$pdo->prepare('UPDATE users SET password_hash = :hash WHERE user_id = :id');
            $update->execute([':hash' => $newHash, ':id' => $user['user_id']]);
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store minimal user data in session
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['timezone']  = $user['timezone'];
        $_SESSION['role_id']   = $user['role_id'];
        $_SESSION['logged_in'] = true;
        $_SESSION['ip']        = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['last_activity'] = time();

        // Remove password_hash before returning
        unset($user['password_hash']);
        return $user;
    }

    /**
     * Destroy session and log out
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Check if a user is authenticated
     */
    public static function check(): bool
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }

        // Session timeout: 30 minutes of inactivity
        $timeout = 1800;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::logout();
            return false;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Get current authenticated user data from session
     *
     * @return array|null
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'user_id'   => $_SESSION['user_id'],
            'username'  => $_SESSION['username'],
            'email'     => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'timezone'  => $_SESSION['timezone'],
            'role_id'   => $_SESSION['role_id'],
        ];
    }

    /**
     * Get a specific field from the authenticated user
     */
    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Hash a password (for registration or password changes)
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
