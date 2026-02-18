<?php
/**
 * Gate - Authorization based on roles, modules, and granular permissions
 *
 * Module-level:
 *   Gate::allows('billing');        // checks role_module
 *
 * Granular permission:
 *   Gate::can('manage_users');      // checks role_permission by perm_key
 *
 * All allowed modules:
 *   Gate::modules();
 */

class Gate
{
    private static ?PDO $pdo = null;

    public static function init(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * Check if the current user has access to a module slug
     */
    public static function allows(string $slug): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $permissions = self::loadModulePermissions((int) $user['role_id']);

        return in_array($slug, $permissions, true);
    }

    /**
     * Check if the current user's role has a granular permission (perm_key)
     */
    public static function can(string $permKey): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $perms = self::loadRolePermissions((int) $user['role_id']);

        return in_array($permKey, $perms, true);
    }

    /**
     * Get all modules the current user has access to
     */
    public static function modules(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $roleId = (int) $user['role_id'];
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
    private static function loadModulePermissions(int $roleId): array
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
     * Load granular perm_keys for a role (cached in session)
     */
    private static function loadRolePermissions(int $roleId): array
    {
        $cacheKey = 'gate_role_perms_' . $roleId;

        if (isset($_SESSION[$cacheKey])) {
            return $_SESSION[$cacheKey];
        }

        if (!self::$pdo) {
            return [];
        }

        $stmt = self::$pdo->prepare(
            'SELECT p.perm_key
             FROM permissions p
             INNER JOIN role_permission rp ON rp.permission_id = p.permission_id
             WHERE rp.role_id = :role_id'
        );
        $stmt->execute([':role_id' => $roleId]);
        $keys = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $_SESSION[$cacheKey] = $keys;

        return $keys;
    }

    /**
     * Check if the current user's role allows deleting records.
     * Only Admin (role_id=1) and Leader (role_id=2) can delete.
     */
    public static function canDelete(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return in_array((int)$user['role_id'], [1, 2], true);
    }

    /**
     * Clear all cached permissions (call after role/permission change)
     */
    public static function clearCache(): void
    {
        foreach (array_keys($_SESSION) as $key) {
            if (str_starts_with($key, 'gate_perms_')
                || str_starts_with($key, 'gate_modules_')
                || str_starts_with($key, 'gate_role_perms_')) {
                unset($_SESSION[$key]);
            }
        }
    }
}
