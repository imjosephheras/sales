-- ============================================================
-- Seed: Test users for role-based access testing
-- Database: form
-- Run: mysql -u root form < seed_test_users.sql
-- Password for all test users: 123456
-- ============================================================

INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `timezone`, `created_at`, `role_id`)
VALUES
    ('leader',       'leader@primefacility.com',       '$2y$12$ol7TDPl10EHq0PoaLXsYtewhPvUJtWmgNeh0qd/dgtAXgVailMGUK', 'Test Leader',       'America/Mexico_City', NOW(), 2),
    ('vendedor',     'vendedor@primefacility.com',     '$2y$12$ol7TDPl10EHq0PoaLXsYtewhPvUJtWmgNeh0qd/dgtAXgVailMGUK', 'Test Vendedor',     'America/Mexico_City', NOW(), 3),
    ('empleado',     'empleado@primefacility.com',     '$2y$12$ol7TDPl10EHq0PoaLXsYtewhPvUJtWmgNeh0qd/dgtAXgVailMGUK', 'Test Empleado',     'America/Mexico_City', NOW(), 4),
    ('contabilidad', 'contabilidad@primefacility.com', '$2y$12$ol7TDPl10EHq0PoaLXsYtewhPvUJtWmgNeh0qd/dgtAXgVailMGUK', 'Test Contabilidad', 'America/Mexico_City', NOW(), 5);
