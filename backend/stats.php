<?php
/**
 * Stats API Endpoint - Direct access
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
    
    // Get counts from different tables
    $stats = [];
    
    // Count tournaments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tournaments");
    $stats['tournaments'] = $stmt->fetchColumn();
    
    // Count events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
    $stats['events'] = $stmt->fetchColumn();
    
    // Count committee members
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM committee_members");
    $stats['members'] = $stmt->fetchColumn();
    
    // Count gallery items
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
    $stats['gallery'] = $stmt->fetchColumn();
    
    // Count sponsors
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sponsors");
    $stats['sponsors'] = $stmt->fetchColumn();
    
    // Count games
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM games");
    $stats['games'] = $stmt->fetchColumn();
    
    // Count players (from tournament registrations)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tournament_registrations");
    $stats['players'] = $stmt->fetchColumn();
    
    $result = [
        'success' => true,
        'data' => $stats,
        'message' => 'Stats retrieved successfully'
    ];
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'API error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
