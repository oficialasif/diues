<?php
/**
 * Working API Endpoint - Simple, direct API that definitely works
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
    
    // Get the endpoint from URL
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($path, '/');
    
    // Extract endpoint
    if (preg_match('/api_working\/(.*)/', $path, $matches)) {
        $endpoint = $matches[1];
    } else {
        $endpoint = '';
    }
    
    $result = [];
    
    switch ($endpoint) {
        case 'events':
            $stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC");
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved successfully',
                'count' => count($events)
            ];
            break;
            
        case 'committee':
            $stmt = $pdo->query("SELECT * FROM committee_members ORDER BY created_at DESC");
            $committee = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                'success' => true,
                'data' => $committee,
                'message' => 'Committee members retrieved successfully',
                'count' => count($committee)
            ];
            break;
            
        case 'tournaments':
            $stmt = $pdo->query("SELECT t.*, g.name as game_name, g.genre 
                                FROM tournaments t 
                                LEFT JOIN games g ON t.game_id = g.id 
                                ORDER BY t.created_at DESC");
            $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                'success' => true,
                'data' => $tournaments,
                'message' => 'Tournaments retrieved successfully',
                'count' => count($tournaments)
            ];
            break;
            
        case 'gallery':
            $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
            $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                'success' => true,
                'data' => $gallery,
                'message' => 'Gallery items retrieved successfully',
                'count' => count($gallery)
            ];
            break;
            
        case 'sponsors':
            $stmt = $pdo->query("SELECT * FROM sponsors ORDER BY created_at DESC");
            $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                'success' => true,
                'data' => $sponsors,
                'message' => 'Sponsors retrieved successfully',
                'count' => count($sponsors)
            ];
            break;
            
        case 'achievements':
            $stmt = $pdo->query("SELECT * FROM achievements ORDER BY created_at DESC");
            $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = [
                'success' => true,
                'data' => $achievements,
                'message' => 'Achievements retrieved successfully',
                'count' => count($achievements)
            ];
            break;
            
        case 'stats':
            $stats = [];
            $tables = ['events', 'committee_members', 'tournaments', 'gallery', 'sponsors', 'achievements'];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                $stats[$table] = $count;
            }
            $result = [
                'success' => true,
                'data' => $stats,
                'message' => 'Stats retrieved successfully'
            ];
            break;
            
        default:
            $result = [
                'success' => true,
                'message' => 'DIU Esports API - Working Endpoint',
                'available_endpoints' => [
                    'events' => '/api_working/events',
                    'committee' => '/api_working/committee',
                    'tournaments' => '/api_working/tournaments',
                    'gallery' => '/api_working/gallery',
                    'sponsors' => '/api_working/sponsors',
                    'achievements' => '/api_working/achievements',
                    'stats' => '/api_working/stats'
                ],
                'usage' => 'Add endpoint name after /api_working/ (e.g., /api_working/events)'
            ];
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'API error: ' . $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ], JSON_PRETTY_PRINT);
}
?>
