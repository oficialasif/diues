<?php
/**
 * DIU Esports Community API
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
$isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'render.com') !== false;

if ($isProduction) {
    // Load production configuration for Render
    require_once '../config/config.production.php';
    require_once '../config/database.production.php';
} else {
    // Load local configuration for XAMPP
    require_once '../config/database.php';
}

require_once '../config/auth.php';

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
            require_once 'handlers/tournaments.php';
            $handler = new TournamentsHandler($database);
            break;
            
        case 'events':
            require_once 'handlers/events.php';
            $handler = new EventsHandler($database);
            break;
            
        case 'committee':
            require_once 'handlers/committee.php';
            $handler = new CommitteeHandler($database);
            break;
            
        case 'gallery':
            require_once 'handlers/gallery.php';
            $handler = new GalleryHandler($database);
            break;
            
        case 'sponsors':
            require_once 'handlers/sponsors.php';
            $handler = new SponsorsHandler($database);
            break;
            
        case 'achievements':
            require_once 'handlers/achievements.php';
            $handler = new AchievementsHandler($database);
            break;
            
        case 'settings':
            require_once 'handlers/settings.php';
            $handler = new SettingsHandler($database);
            break;
            
        case 'auth':
            require_once 'handlers/auth.php';
            $handler = new AuthHandler($database, $auth);
            break;
            
        case 'stats':
            require_once 'handlers/stats.php';
            $handler = new StatsHandler($database);
            break;
        case 'countdown':
            require_once 'handlers/countdown.php';
            $handler = new CountdownHandler($database);
            break;
            
        default:
            apiError('Resource not found', 404);
    }
    
    // Handle the request based on method
    switch ($method) {
        case 'GET':
            if ($id && $action) {
                if ($action === 'registrations') {
                    $result = $handler->getRegistrations($id);
                } else {
                    $result = $handler->getAction($id, $action);
                }
            } elseif ($id) {
                $result = $handler->get($id);
            } else {
                if ($action === 'registrations') {
                    $result = $handler->getAllRegistrations();
                } else {
                    $result = $handler->getAll();
                }
            }
            break;
            
        case 'POST':
            if ($action) {
                if ($action === 'register') {
                    $result = $handler->register();
                } else {
                    $result = $handler->postAction($action);
                }
            } elseif ($id && !is_numeric($id)) {
                // Handle cases like /tournaments/register where 'register' is not a numeric ID
                $action = $id;
                if ($action === 'register') {
                    $result = $handler->register();
                } else {
                    $result = $handler->postAction($action);
                }
            } else {
                $result = $handler->create();
            }
            break;
            
        case 'PUT':
            if (!$id) {
                apiError('ID required for update', 400);
            }
            $result = $handler->update($id);
            break;
            
        case 'DELETE':
            if (!$id) {
                apiError('ID required for deletion', 400);
            }
            $result = $handler->delete($id);
            break;
            
        default:
            apiError('Method not allowed', 405);
    }
    
    // Return the result
    if (isset($result['success']) && $result['success']) {
        apiResponse($result['data'], 200, $result['message'] ?? 'Success');
    } else {
        apiError($result['message'] ?? 'Operation failed', 400);
    }
    
} catch (Exception $e) {
    apiError('Server error: ' . $e->getMessage(), 500);
}
?>
