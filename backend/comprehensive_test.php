<?php
/**
 * Comprehensive API Test - Test everything systematically
 */

header('Content-Type: application/json');

// Determine if we're in production or development
$isProduction = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'render.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false ||
    (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
);

$results = [
    'test_info' => [
        'timestamp' => date('c'),
        'environment' => $isProduction ? 'production' : 'development',
        'server_host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'php_version' => PHP_VERSION
    ],
    'tests' => []
];

// Test 1: Basic PHP functionality
$results['tests']['php_basic'] = [
    'name' => 'Basic PHP Test',
    'status' => 'success',
    'message' => 'PHP is working correctly'
];

// Test 2: File system access
$results['tests']['file_system'] = [
    'name' => 'File System Test',
    'status' => file_exists(__DIR__ . '/config/database.production.php') ? 'success' : 'error',
    'message' => file_exists(__DIR__ . '/config/database.production.php') ? 'Config files accessible' : 'Config files not found'
];

// Test 3: Database connection
try {
    if ($isProduction) {
        require_once __DIR__ . '/config/config.production.php';
        require_once __DIR__ . '/config/database.production.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    $results['tests']['database_connection'] = [
        'name' => 'Database Connection',
        'status' => 'success',
        'message' => 'Database connected successfully'
    ];
    
    // Test 4: Database tables
    $tables = ['events', 'committee_members', 'tournaments', 'gallery', 'sponsors', 'achievements'];
    $tableResults = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            $tableResults[$table] = [
                'exists' => true,
                'count' => $count
            ];
        } catch (Exception $e) {
            $tableResults[$table] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    $results['tests']['database_tables'] = [
        'name' => 'Database Tables',
        'status' => 'success',
        'data' => $tableResults
    ];
    
    // Test 5: API Handlers
    $handlerResults = [];
    $handlerFiles = [
        'events' => __DIR__ . '/api/handlers/events.php',
        'committee' => __DIR__ . '/api/handlers/committee.php',
        'tournaments' => __DIR__ . '/api/handlers/tournaments.php',
        'gallery' => __DIR__ . '/api/handlers/gallery.php',
        'sponsors' => __DIR__ . '/api/handlers/sponsors.php',
        'achievements' => __DIR__ . '/api/handlers/achievements.php'
    ];
    
    foreach ($handlerFiles as $name => $file) {
        if (file_exists($file)) {
            try {
                require_once $file;
                $className = ucfirst($name) . 'Handler';
                if (class_exists($className)) {
                    $handler = new $className($database);
                    $result = $handler->getAll();
                    $handlerResults[$name] = [
                        'file_exists' => true,
                        'class_exists' => true,
                        'method_works' => true,
                        'data_count' => is_array($result['data']) ? count($result['data']) : 0,
                        'success' => $result['success'] ?? false
                    ];
                } else {
                    $handlerResults[$name] = [
                        'file_exists' => true,
                        'class_exists' => false,
                        'error' => "Class $className not found"
                    ];
                }
            } catch (Exception $e) {
                $handlerResults[$name] = [
                    'file_exists' => true,
                    'class_exists' => true,
                    'method_works' => false,
                    'error' => $e->getMessage()
                ];
            }
        } else {
            $handlerResults[$name] = [
                'file_exists' => false,
                'error' => 'Handler file not found'
            ];
        }
    }
    
    $results['tests']['api_handlers'] = [
        'name' => 'API Handlers',
        'status' => 'success',
        'data' => $handlerResults
    ];
    
    // Test 6: Sample data retrieval
    $sampleData = [];
    try {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 1");
        $sampleData['events'] = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $sampleData['events'] = ['error' => $e->getMessage()];
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM committee_members ORDER BY created_at DESC LIMIT 1");
        $sampleData['committee'] = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $sampleData['committee'] = ['error' => $e->getMessage()];
    }
    
    $results['tests']['sample_data'] = [
        'name' => 'Sample Data Retrieval',
        'status' => 'success',
        'data' => $sampleData
    ];
    
} catch (Exception $e) {
    $results['tests']['database_connection'] = [
        'name' => 'Database Connection',
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ];
}

// Test 7: Environment variables
$envVars = [];
$envKeys = ['DB_HOST', 'DB_NAME', 'DB_USERNAME', 'APP_ENV', 'FRONTEND_URL'];
foreach ($envKeys as $key) {
    $envVars[$key] = $_ENV[$key] ?? 'Not set';
}

$results['tests']['environment'] = [
    'name' => 'Environment Variables',
    'status' => 'success',
    'data' => $envVars
];

// Test 8: File permissions
$permissions = [];
$testFiles = [
    'api/index.php',
    'api/handlers/events.php',
    'config/database.production.php',
    '.htaccess'
];

foreach ($testFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    $permissions[$file] = [
        'exists' => file_exists($fullPath),
        'readable' => is_readable($fullPath),
        'size' => file_exists($fullPath) ? filesize($fullPath) : 0
    ];
}

$results['tests']['file_permissions'] = [
    'name' => 'File Permissions',
    'status' => 'success',
    'data' => $permissions
];

// Overall status
$hasErrors = false;
foreach ($results['tests'] as $test) {
    if ($test['status'] === 'error') {
        $hasErrors = true;
        break;
    }
}

$results['overall_status'] = $hasErrors ? 'error' : 'success';
$results['summary'] = $hasErrors ? 'Some tests failed - see individual test results' : 'All tests passed successfully';

echo json_encode($results, JSON_PRETTY_PRINT);
?>
