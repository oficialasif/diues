<?php
/**
 * Tournament Registration API Endpoint - Direct access
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

try {
    if ($isProduction) {
        require_once __DIR__ . '/config/config.production.php';
        require_once __DIR__ . '/config/database.production.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle tournament registration
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON data');
        }
        
        $tournament_id = intval($input['tournament_id'] ?? 0);
        $player_name = trim($input['player_name'] ?? '');
        $player_email = trim($input['player_email'] ?? '');
        $player_phone = trim($input['player_phone'] ?? '');
        $player_ign = trim($input['player_ign'] ?? '');
        $team_name = trim($input['team_name'] ?? '');
        $team_members = $input['team_members'] ?? [];
        $additional_info = trim($input['additional_info'] ?? '');
        
        // Validation
        if (empty($tournament_id) || empty($player_name) || empty($player_email)) {
            throw new Exception('Tournament ID, player name, and email are required');
        }
        
        // Check if tournament exists
        $stmt = $pdo->prepare("SELECT id, name FROM tournaments WHERE id = ?");
        $stmt->execute([$tournament_id]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tournament) {
            throw new Exception('Tournament not found');
        }
        
        // Insert registration
        $stmt = $pdo->prepare("
            INSERT INTO registrations (
                tournament_id, player_name, player_email, player_phone, 
                player_ign, team_name, team_members, additional_info, 
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $team_members_json = json_encode($team_members);
        
        $stmt->execute([
            $tournament_id, $player_name, $player_email, $player_phone,
            $player_ign, $team_name, $team_members_json, $additional_info
        ]);
        
        $registration_id = $pdo->lastInsertId();
        
        $result = [
            'success' => true,
            'message' => 'Registration submitted successfully!',
            'data' => [
                'id' => $registration_id,
                'tournament_id' => $tournament_id,
                'tournament_name' => $tournament['name'],
                'player_name' => $player_name,
                'player_email' => $player_email,
                'status' => 'pending'
            ]
        ];
        
    } else {
        // Handle GET request - return registrations
        $stmt = $pdo->query("
            SELECT r.*, t.name as tournament_name 
            FROM registrations r 
            JOIN tournaments t ON r.tournament_id = t.id 
            ORDER BY r.created_at DESC
        ");
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [
            'success' => true,
            'data' => $registrations,
            'message' => 'Registrations retrieved successfully',
            'count' => count($registrations)
        ];
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
