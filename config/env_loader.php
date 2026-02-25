<?php
// ============================================================
// env_loader.php - Cargador de variables de entorno (.env)
// ============================================================
// Lee el archivo .env en la raiz del proyecto y carga las
// variables en el entorno usando putenv() y $_ENV.
// Compatible con PHP puro, sin dependencias externas.
// ============================================================

/**
 * Load environment variables from a .env file.
 *
 * Supports:
 *   - KEY=value pairs
 *   - Quoted values (single and double quotes)
 *   - Comments (lines starting with #)
 *   - Empty lines (skipped)
 *
 * Does NOT override variables that are already set in the
 * environment, so system-level env vars take precedence.
 *
 * @param string|null $path Path to the .env file. Defaults to project root.
 * @return void
 */
function loadEnv($path = null) {
    if ($path === null) {
        $path = __DIR__ . '/../.env';
    }

    if (!file_exists($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // Must contain an = sign
        $eqPos = strpos($line, '=');
        if ($eqPos === false) {
            continue;
        }

        $key   = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));

        // Skip if key is empty
        if ($key === '') {
            continue;
        }

        // Remove surrounding quotes from value
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // Do not override existing environment variables
        if (getenv($key) !== false) {
            continue;
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Auto-load when this file is included
loadEnv();
