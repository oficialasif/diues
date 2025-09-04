<?php
/**
 * Gallery API Endpoint - Direct access
 */

header('Content-Type: application/json');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Determine if we're in production or development
$isProduction = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'render.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false ||
    (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
);

try {
    if ($isProduction) {
        require_once __DIR__ . '/config/config.production.php';
        require_once __DIR__ . '/config/database.production.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
    $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [
        'success' => true,
        'data' => $gallery,
        'message' => 'Gallery items retrieved successfully',
        'count' => count($gallery)
    ];
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'API error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
