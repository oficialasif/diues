<?php
/**
 * Production Configuration for DIU Esports Community Portal
 * Loads environment variables and sets production settings
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env.production')) {
    $env = parse_ini_file(__DIR__ . '/../.env.production');
    if ($env !== false && is_array($env)) {
        foreach ($env as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Production settings
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);
define('APP_URL', $_ENV['APP_URL'] ?? 'https://diu-esports-backend.onrender.com');
define('FRONTEND_URL', $_ENV['FRONTEND_URL'] ?? 'https://diues.vercel.app');

// Database settings
define('DB_DRIVER', $_ENV['DB_DRIVER'] ?? 'pgsql');
define('DB_HOST', $_ENV['DB_HOST'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? '');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');

// File storage settings
define('STORAGE_TYPE', $_ENV['STORAGE_TYPE'] ?? 'local');
define('UPLOAD_PATH', $_ENV['UPLOAD_PATH'] ?? 'uploads/');
define('MAX_FILE_SIZE', $_ENV['MAX_FILE_SIZE'] ?? 5242880); // 5MB
define('ALLOWED_FILE_TYPES', $_ENV['ALLOWED_FILE_TYPES'] ?? 'image/jpeg,image/png,image/gif,image/webp');

// CORS settings
define('CORS_ALLOWED_ORIGINS', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'https://diues.vercel.app,http://localhost:3000');

// Session settings
define('SESSION_SECURE', $_ENV['SESSION_SECURE'] ?? true);
define('SESSION_HTTP_ONLY', $_ENV['SESSION_HTTP_ONLY'] ?? true);
define('SESSION_SAME_SITE', $_ENV['SESSION_SAME_SITE'] ?? 'Strict');

// Logging
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'error');
define('TIMEZONE', $_ENV['TIMEZONE'] ?? 'UTC');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Log errors to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
?>
