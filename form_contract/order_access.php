<?php
// ============================================================
// order_access.php - Centralized RBAC for orders
//
// Provides seller-based filtering depending on the authenticated
// user's role:
//   - Admin  (role_id=1) and Leader (role_id=2): Full access
//   - Vendedor (role_id=3): Only orders where seller = full_name
//   - Other roles: Handled by module middleware (no contracts access)
// ============================================================

require_once __DIR__ . '/../app/bootstrap.php';

// Role IDs that have unrestricted access to all orders
define('RBAC_FULL_ACCESS_ROLES', [1, 2]); // Admin, Leader

/**
 * Enforce authentication and module access for order API endpoints.
 * Returns the authenticated user array or terminates with JSON error.
 *
 * @return array User data from Auth::user()
 */
function requireOrderAccess(): array
{
    Middleware::module('contracts');

    $user = Auth::user();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    return $user;
}

/**
 * Build an SQL fragment + parameters for seller-based RBAC filtering.
 *
 * Usage:
 *   $rbac = getOrderRbacFilter($user);
 *   $sql  = "SELECT * FROM forms WHERE status IN ('draft','pending') " . $rbac['sql'];
 *   $stmt->execute(array_merge($otherParams, $rbac['params']));
 *
 * @param  array $user  User data from Auth::user()
 * @return array{sql: string, params: array}
 */
function getOrderRbacFilter(array $user): array
{
    if (in_array((int) $user['role_id'], RBAC_FULL_ACCESS_ROLES, true)) {
        return ['sql' => '', 'params' => []];
    }

    // Vendedor and any other role: restrict to own orders
    return [
        'sql'    => 'AND seller = :rbac_seller',
        'params' => [':rbac_seller' => $user['full_name']],
    ];
}

/**
 * Check whether the current user is allowed to access a specific form.
 *
 * @param  PDO   $pdo
 * @param  int   $formId
 * @param  array $user  User data from Auth::user()
 * @return bool
 */
function canAccessOrder(PDO $pdo, int $formId, array $user): bool
{
    if (in_array((int) $user['role_id'], RBAC_FULL_ACCESS_ROLES, true)) {
        return true;
    }

    $stmt = $pdo->prepare('SELECT seller FROM forms WHERE form_id = ?');
    $stmt->execute([$formId]);
    $form = $stmt->fetch();

    if (!$form) {
        return false;
    }

    return $form['seller'] === $user['full_name'];
}

/**
 * Deny access with a JSON 403 response and terminate.
 */
function denyOrderAccess(): void
{
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied: you do not have permission to access this order.',
    ]);
    exit;
}
