<?php
/**
 * Brevo SMTP Configuration
 *
 * Credentials are loaded from the .env file.
 * See .env.example for the required keys.
 */

require_once __DIR__ . '/config/env_loader.php';

return [
    'smtp_host'      => getenv('SMTP_HOST')      ?: 'smtp-relay.brevo.com',
    'smtp_port'      => (int)(getenv('SMTP_PORT') ?: 587),
    'smtp_username'  => getenv('SMTP_USERNAME')   ?: '',
    'smtp_password'  => getenv('SMTP_PASSWORD')   ?: '',
    'smtp_encryption'=> getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_email'     => getenv('MAIL_FROM_EMAIL') ?: '',
    'from_name'      => getenv('MAIL_FROM_NAME')  ?: 'Sales Management System',
    'to_email'       => getenv('MAIL_TO_EMAIL')   ?: '',
    'to_name'        => getenv('MAIL_TO_NAME')    ?: 'Administrator',
];
