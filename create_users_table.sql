-- ============================================================
-- Migration: Create users table for authentication system
-- Database: form
-- Run: mysql -u root form < create_users_table.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `user_id`       INT AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(100) NOT NULL UNIQUE,
    `email`         VARCHAR(200) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name`     VARCHAR(200) NOT NULL,
    `timezone`      VARCHAR(50)  DEFAULT 'America/Chicago',
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `role_id`       INT          DEFAULT 1,
    INDEX `idx_email` (`email`),
    INDEX `idx_role`  (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user (password: admin123)
-- IMPORTANT: Change this password immediately after first login
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role_id`)
SELECT 'admin', 'admin@primefacility.com',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       'Administrator', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'admin');
