<?php
/**
 * DIU Esports Community Portal API
 * Main router for all API endpoints
 */

// Handle CORS
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Helper function for sending JSON response
function apiResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Helper function for sending Error response
function apiError($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

// Basic router
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Parse URL to get resource
// URL format: /api/resource/id
$basePath = '/api';
if (strpos($requestUri, $basePath) === 0) {
    $path = substr($requestUri, strlen($basePath));
} else {
    $path = $requestUri;
}

$parts = explode('/', trim($path, '/'));
$resource = $parts[0] ?? '';
$id = $parts[1] ?? null;

// Route the request
switch ($resource) {
    case '':
        apiResponse(['message' => 'DIU Esports API Running', 'version' => '1.0']);
        break;
        
    case 'health':
        apiResponse(['status' => 'ok']);
        break;

    case 'games':
        require_once __DIR__ . '/../games.php';
        $handler = new GamesHandler();
        if ($method === 'GET') {
            if ($id) $handler->getById($id);
            else $handler->getAll();
        } elseif ($method === 'POST') {
            // Check auth (simplified for now)
            $data = json_decode(file_get_contents('php://input'), true);
            $handler->create($data);
        }
        break;
        
    case 'tournaments':
        require_once __DIR__ . '/../tournaments.php';
        $handler = new TournamentsHandler();
        if ($method === 'GET') $handler->getAll();
        break;
        
    case 'tournaments-register':
         require_once __DIR__ . '/../tournaments_register.php';
         $handler = new TournamentRegistrationHandler();
         if ($method === 'POST') {
             $data = json_decode(file_get_contents('php://input'), true);
             $handler->register($data);
         }
         break;

    case 'events':
        require_once __DIR__ . '/../events.php';
        $handler = new EventsHandler();
        if ($method === 'GET') $handler->getAll();
        break;
        
    case 'committee':
        require_once __DIR__ . '/../committee.php';
        $handler = new CommitteeHandler();
        if ($method === 'GET') $handler->getAll();
        break;

    case 'gallery':
        require_once __DIR__ . '/../gallery.php';
        $handler = new GalleryHandler();
        if ($method === 'GET') $handler->getAll();
        break;
        
    case 'sponsors':
        require_once __DIR__ . '/../sponsors.php';
        $handler = new SponsorsHandler();
        if ($method === 'GET') $handler->getAll();
        break;
        
    case 'settings':
        require_once __DIR__ . '/../settings.php';
        $handler = new SettingsHandler();
        if ($method === 'GET') $handler->getAll();
        break;
        
    case 'achievements':
        require_once __DIR__ . '/../achievements.php';
        $handler = new AchievementsHandler();
        if ($method === 'GET') $handler->getAll();
        break;

    case 'stats':
        require_once __DIR__ . '/../stats.php';
        $handler = new StatsHandler();
        if ($method === 'GET') $handler->getAll();
        break;

    case 'about':
        require_once __DIR__ . '/../about.php';
        $handler = new AboutHandler();
        if ($method === 'GET') $handler->getAll();
        break;

    default:
        // Try file based routing for simple scripts if exists in api folder
        if (file_exists(__DIR__ . "/$resource.php")) {
            require_once __DIR__ . "/$resource.php";
        } else {
            apiError('Endpoint not found', 404);
        }
        break;
}
?>
