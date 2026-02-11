<?php
/**
 * Gate - Modular authorization based on roles and modules
 *
 * Checks if the current user's role has access to a given module.
 * Caches permissions in session to avoid repeated DB queries.
 *
 * Usage:
 *   Gate::allows('billing');        // true/false
 *   Gate::modules();               // array of allowed modules for current user
 */

class Gate
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
     * Check if the current user has access to a module
     */
    public static function allows(string $slug): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $permissions = self::loadPermissions((int) $user['role_id']);

        return in_array($slug, $permissions, true);
    }

    /**
     * Get all modules the current user has access to (with name, slug, icon, url)
     *
     * @return array List of module rows the user can access
     */
    public static function modules(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $roleId = (int) $user['role_id'];

        // Check session cache
        $cacheKey = 'gate_modules_' . $roleId;
        if (isset($_SESSION[$cacheKey])) {
            return $_SESSION[$cacheKey];
        }

        if (!self::$pdo) {
            return [];
        }

        $stmt = self::$pdo->prepare(
            'SELECT m.module_id, m.name, m.slug, m.icon, m.url
             FROM modules m
             INNER JOIN role_module rm ON rm.module_id = m.module_id
             WHERE rm.role_id = :role_id
             ORDER BY m.module_id ASC'
        );
        $stmt->execute([':role_id' => $roleId]);
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $_SESSION[$cacheKey] = $modules;

        return $modules;
    }

    /**
     * Load allowed module slugs for a role (cached in session)
     */
    private static function loadPermissions(int $roleId): array
    {
        $cacheKey = 'gate_perms_' . $roleId;

        if (isset($_SESSION[$cacheKey])) {
            return $_SESSION[$cacheKey];
        }

        if (!self::$pdo) {
            return [];
        }

        $stmt = self::$pdo->prepare(
            'SELECT m.slug
             FROM modules m
             INNER JOIN role_module rm ON rm.module_id = m.module_id
             WHERE rm.role_id = :role_id'
        );
        $stmt->execute([':role_id' => $roleId]);
        $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $_SESSION[$cacheKey] = $slugs;

        return $slugs;
    }

    /**
     * Clear cached permissions (call after role change)
     */
    public static function clearCache(): void
    {
        foreach (array_keys($_SESSION) as $key) {
            if (str_starts_with($key, 'gate_perms_') || str_starts_with($key, 'gate_modules_')) {
                unset($_SESSION[$key]);
            }
        }
    }
}
