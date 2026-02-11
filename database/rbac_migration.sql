-- ============================================================
-- RBAC Migration - Roles, Permissions, Role-Permission
-- Run this ONCE against the 'form' database.
-- bootstrap.php also auto-creates these tables on startup.
-- ============================================================

-- 1. Add description & created_at to roles if missing
ALTER TABLE `roles`
    ADD COLUMN IF NOT EXISTS `description` VARCHAR(255) DEFAULT '' AFTER `name`,
    ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `description`;

-- 2. Permissions table
CREATE TABLE IF NOT EXISTS `permissions` (
    `permission_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100) NOT NULL,
    `description`   VARCHAR(255) DEFAULT '',
    `perm_key`      VARCHAR(100) NOT NULL UNIQUE,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Role ↔ Permission junction
CREATE TABLE IF NOT EXISTS `role_permission` (
    `role_id`       INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`)       REFERENCES `roles`(`role_id`)             ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`permission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Add photo column to users
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `photo` VARCHAR(255) DEFAULT NULL AFTER `full_name`;

-- 5. Seed default permissions
INSERT IGNORE INTO `permissions` (`name`, `description`, `perm_key`) VALUES
    ('Manage Users',       'Create, edit and list users',       'manage_users'),
    ('Manage Roles',       'Create, edit and list roles',       'manage_roles'),
    ('Manage Permissions', 'Create, edit and list permissions', 'manage_permissions'),
    ('View Reports',       'Access the reports module',         'view_reports'),
    ('Manage Billing',     'Access billing / accounting',       'manage_billing'),
    ('Manage Calendar',    'Access the calendar module',        'manage_calendar');

-- 6. Assign all permissions to Admin role (role_id = 1)
INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`)
    SELECT 1, `permission_id` FROM `permissions`;
