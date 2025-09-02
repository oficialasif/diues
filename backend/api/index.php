<?php
/**
 * DIU Esports Community Portal API
 * Main router for all API endpoints
 */

header('Content-Type: application/json');

// CORS headers - Allow localhost and production
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
    // Load production configuration for Render
    require_once __DIR__ . '/../config/config.production.php';
    require_once __DIR__ . '/../config/database.production.php';
} else {
    // Load local configuration for XAMPP
    require_once __DIR__ . '/../config/database.php';
}

require_once __DIR__ . '/../config/auth.php';

$database = new Database();
$auth = new Auth($database);

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract the path after /api
if (preg_match('/\/api\/(.*)/', $path, $matches)) {
    $path = $matches[1];
} else {
    $path = '';
}
$path = trim($path, '/');

// Parse path segments
$segments = explode('/', $path);
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;
$action = $segments[2] ?? null;

// Debug logging
error_log("API Debug - Path: '$path', Resource: '$resource', ID: '$id', Action: '$action', Method: '$method'");

// API Response helper function
function apiResponse($data = null, $status = 200, $message = 'Success') {
    http_response_code($status);
    echo json_encode([
        'success' => $status < 400,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c')
    ]);
    exit();
}

// Error response helper
function apiError($message, $status = 400) {
    apiResponse(null, $status, $message);
}

// Handle base API endpoint
if (empty($resource)) {
    apiResponse([
        'message' => 'DIU Esports Community Portal API',
        'version' => '1.0.0',
        'endpoints' => [
            'tournaments' => '/api/tournaments',
            'events' => '/api/events',
            'committee' => '/api/committee',
            'gallery' => '/api/gallery',
            'sponsors' => '/api/sponsors',
            'achievements' => '/api/achievements',
            'settings' => '/api/settings',
            'stats' => '/api/stats',
            'countdown' => '/api/countdown',
            'auth' => '/api/auth'
        ],
        'documentation' => 'Check individual endpoints for detailed information'
    ], 200, 'API is running');
}

// Validate resource
$validResources = ['tournaments', 'events', 'committee', 'gallery', 'sponsors', 'achievements', 'settings', 'auth', 'stats', 'countdown'];
if (!in_array($resource, $validResources)) {
    apiError('Invalid resource', 404);
}

// Route the request
try {
    switch ($resource) {
        case 'tournaments':
            require_once __DIR__ . '/handlers/tournaments.php';
            $handler = new TournamentsHandler($database);
            break;
            
        case 'events':
            require_once __DIR__ . '/handlers/events.php';
            $handler = new EventsHandler($database);
            break;
            
        case 'committee':
            require_once __DIR__ . '/handlers/committee.php';
            $handler = new CommitteeHandler($database);
            break;
            
        case 'gallery':
            require_once __DIR__ . '/handlers/gallery.php';
            $handler = new GalleryHandler($database);
            break;
            
        case 'sponsors':
            require_once __DIR__ . '/handlers/sponsors.php';
            $handler = new SponsorsHandler($database);
            break;
            
        case 'achievements':
            require_once __DIR__ . '/handlers/achievements.php';
            $handler = new AchievementsHandler($database);
            break;
            
        case 'settings':
            require_once __DIR__ . '/handlers/settings.php';
            $handler = new SettingsHandler($database);
            break;
            
        case 'auth':
            require_once __DIR__ . '/handlers/auth.php';
            $handler = new AuthHandler($database);
            break;
            
        case 'stats':
            require_once __DIR__ . '/handlers/stats.php';
            $handler = new StatsHandler($database);
            break;
            
        case 'countdown':
            require_once __DIR__ . '/handlers/countdown.php';
            $handler = new CountdownHandler($database);
            break;
            
        default:
            apiError('Resource not found', 404);
    }
    
    // Handle the request
    if (isset($handler)) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $handler->get($id);
                } else {
                    $result = $handler->getAll();
                }
                break;
                
            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);
                if ($action === 'login') {
                    $result = $handler->login($input);
                } else {
                    $result = $handler->create($input);
                }
                break;
                
            case 'PUT':
                $input = json_decode(file_get_contents('php://input'), true);
                $result = $handler->update($id, $input);
                break;
                
            case 'DELETE':
                $result = $handler->delete($id);
                break;
                
            default:
                apiError('Method not allowed', 405);
        }
        
        if (isset($result)) {
            apiResponse($result);
        }
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    apiError('Internal server error: ' . $e->getMessage(), 500);
}
?>
