<?php
/**
 * Csrf - Cross-Site Request Forgery protection
 *
 * Generates and validates per-session CSRF tokens.
 * Equivalent to Laravel's @csrf / VerifyCsrfToken middleware.
 */

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Generate or retrieve the current CSRF token
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Output a hidden input field with the CSRF token
     * Usage in forms: <?= Csrf::field() ?>
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    /**
     * Validate the CSRF token from a POST request
     *
     * Uses hash_equals for timing-attack safe comparison
     */
    public static function validate(): bool
    {
        $sessionToken = $_SESSION[self::TOKEN_KEY] ?? '';
        $requestToken = $_POST[self::TOKEN_KEY] ?? '';

        if (empty($sessionToken) || empty($requestToken)) {
            return false;
        }

        return hash_equals($sessionToken, $requestToken);
    }

    /**
     * Validate and abort with 403 if invalid
     */
    public static function validateOrFail(): void
    {
        if (!self::validate()) {
            http_response_code(403);
            die('403 Forbidden - Invalid CSRF token.');
        }
    }

    /**
     * Regenerate the CSRF token (call after successful form submission)
     */
    public static function regenerate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
    }
}
