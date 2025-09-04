<?php
/**
 * Countdown API Endpoint - Direct access
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
    
    // Get countdown settings
    $stmt = $pdo->query("SELECT * FROM event_countdown_settings ORDER BY created_at DESC LIMIT 1");
    $countdownSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$countdownSettings) {
        // Return default countdown settings if none exist
        $countdownSettings = [
            'id' => 1,
            'status_text' => 'Next Event Coming Soon',
            'custom_message' => 'Stay tuned for our next exciting event!',
            'target_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'is_active' => true,
            'show_countdown' => true,
            'countdown_type' => 'days',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    $result = [
        'success' => true,
        'data' => $countdownSettings,
        'message' => 'Countdown settings retrieved successfully'
    ];
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'API error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
