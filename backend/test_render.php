<?php
/**
 * Test file for Render deployment
 * This file helps verify that the deployment and database connection are working
 */

// Load production configuration
require_once __DIR__ . '/config/config.production.php';
require_once __DIR__ . '/config/database.production.php';

header('Content-Type: application/json');

try {
    // Test database connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Test basic query
    $stmt = $pdo->query("SELECT version() as db_version");
    $version = $stmt->fetch();
    
    // Test if tables exist
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'DIU Esports Backend is running on Render!',
        'database' => [
            'connected' => true,
            'version' => $version['db_version'],
            'tables_count' => count($tables),
            'tables' => array_column($tables, 'table_name')
        ],
        'environment' => [
            'app_env' => $_ENV['APP_ENV'] ?? 'not_set',
            'db_driver' => $_ENV['DB_DRIVER'] ?? 'not_set',
            'db_host' => $_ENV['DB_HOST'] ?? 'not_set',
            'db_name' => $_ENV['DB_NAME'] ?? 'not_set'
        ],
        'timestamp' => date('c'),
        'render_port' => $_ENV['PORT'] ?? 'not_set'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Backend test failed',
        'error' => $e->getMessage(),
        'environment' => [
            'app_env' => $_ENV['APP_ENV'] ?? 'not_set',
            'db_driver' => $_ENV['DB_DRIVER'] ?? 'not_set',
            'db_host' => $_ENV['DB_HOST'] ?? 'not_set',
            'db_name' => $_ENV['DB_NAME'] ?? 'not_set'
        ],
        'timestamp' => date('c')
    ]);
}
?>
