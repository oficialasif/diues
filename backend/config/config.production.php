<?php
/**
 * Production Configuration for DIU Esports Community
 * Environment-specific settings for Render deployment
 */

// Load environment variables
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Production settings
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? 'false');
define('APP_URL', $_ENV['APP_URL'] ?? 'https://your-app-name.onrender.com');
define('FRONTEND_URL', $_ENV['FRONTEND_URL'] ?? 'https://your-vercel-app.vercel.app');

// Database configuration
define('DB_DRIVER', $_ENV['DB_DRIVER'] ?? 'pgsql');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'diu_esports');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? '');

// File storage configuration
define('STORAGE_TYPE', $_ENV['STORAGE_TYPE'] ?? 'local');
define('UPLOAD_PATH', $_ENV['UPLOAD_PATH'] ?? 'uploads/');
define('ALLOWED_FILE_TYPES', $_ENV['ALLOWED_FILE_TYPES'] ?? 'image/jpeg,image/png,image/gif,image/webp');
define('MAX_FILE_SIZE', $_ENV['MAX_FILE_SIZE'] ?? 5242880);

// Security settings
define('SESSION_SECURE', true);
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Strict');

// CORS configuration
define('CORS_ALLOWED_ORIGINS', [
    FRONTEND_URL,
    'http://localhost:3000', // Local development
    'http://localhost:3001'  // Alternative local port
]);

// Error reporting
if (APP_DEBUG === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/error.log');

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Session configuration
ini_set('session.cookie_secure', SESSION_SECURE);
ini_set('session.cookie_httponly', SESSION_HTTP_ONLY);
ini_set('session.cookie_samesite', SESSION_SAME_SITE);
ini_set('session.gc_maxlifetime', 28800); // 8 hours

// Timezone
date_default_timezone_set('UTC');

// Security headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// CORS headers
function setCorsHeaders($origin = null) {
    if (!$origin) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    }
    
    if (in_array($origin, CORS_ALLOWED_ORIGINS)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Initialize security and CORS
setSecurityHeaders();
setCorsHeaders();

// Helper functions
function isProduction() {
    return APP_ENV === 'production';
}

function isDebug() {
    return APP_DEBUG === 'true';
}

function getConfig($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

function logError($message, $context = []) {
    if (isDebug()) {
        error_log($message . ' ' . json_encode($context));
    }
}

function logInfo($message, $context = []) {
    if (isDebug()) {
        error_log('INFO: ' . $message . ' ' . json_encode($context));
    }
}
?>
