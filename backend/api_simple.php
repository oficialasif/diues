<?php
/**
 * Simple API Endpoint - Direct access to API without routing
 */

header('Content-Type: application/json');

// CORS headers
$allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:3001',
    'https://diues.vercel.app'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: *');
}

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

if ($isProduction) {
    require_once __DIR__ . '/config/config.production.php';
    require_once __DIR__ . '/config/database.production.php';
} else {
    require_once __DIR__ . '/config/database.php';
}

require_once __DIR__ . '/api/handlers/events.php';
require_once __DIR__ . '/api/handlers/committee.php';
require_once __DIR__ . '/api/handlers/tournaments.php';

// Get the endpoint from URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// Extract endpoint
if (preg_match('/api_simple\/(.*)/', $path, $matches)) {
    $endpoint = $matches[1];
} else {
    $endpoint = '';
}

try {
    $database = new Database();
    
    switch ($endpoint) {
        case 'events':
            $handler = new EventsHandler($database);
            $result = $handler->getAll();
            break;
            
        case 'committee':
            $handler = new CommitteeHandler($database);
            $result = $handler->getAll();
            break;
            
        case 'tournaments':
            $handler = new TournamentsHandler($database);
            $result = $handler->getAll();
            break;
            
        default:
            $result = [
                'success' => false,
                'message' => 'Available endpoints: events, committee, tournaments',
                'usage' => 'Use: /api_simple/events, /api_simple/committee, /api_simple/tournaments'
            ];
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'API error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
