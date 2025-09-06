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
        
        // Debug: Log received data
        error_log('Registration data received: ' . json_encode($input));
        
        $tournament_id = intval($input['tournament_id'] ?? 0);
        
        // Handle both field name formats (frontend uses captain_*, backend expects player_*)
        $player_name = trim($input['player_name'] ?? $input['captain_name'] ?? '');
        $player_email = trim($input['player_email'] ?? $input['captain_email'] ?? '');
        $player_phone = trim($input['player_phone'] ?? $input['captain_phone'] ?? '');
        $player_ign = trim($input['player_ign'] ?? $input['captain_game_username'] ?? '');
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
        
        // Determine team type based on team members
        $team_type = 'solo';
        if (!empty($team_members) && is_array($team_members)) {
            $team_type = count($team_members) === 1 ? 'duo' : 'squad';
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Insert registration
            $stmt = $pdo->prepare("
                INSERT INTO tournament_registrations (
                    tournament_id, team_name, team_type, captain_name, captain_email, 
                    captain_phone, captain_discord, captain_student_id, captain_department, 
                    captain_semester, status, registration_date, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)
            ");
            
            $stmt->execute([
                $tournament_id, $team_name, $team_type, $player_name, $player_email,
                $player_phone, '', '', '', '', $additional_info
            ]);
            
            $registration_id = $pdo->lastInsertId();
            
            // Insert captain as first team member
            $stmt = $pdo->prepare("
                INSERT INTO tournament_team_members (
                    registration_id, player_name, player_email, player_phone, player_discord, 
                    player_student_id, player_department, player_semester, player_role, game_username
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'captain', ?)
            ");
            
            $stmt->execute([
                $registration_id, $player_name, $player_email, $player_phone, '', '', '', '', $player_ign
            ]);
            
            // Insert additional team members if provided
            if (!empty($team_members) && is_array($team_members)) {
                foreach ($team_members as $member) {
                    if (!empty($member['player_name'])) {
                        $stmt = $pdo->prepare("
                            INSERT INTO tournament_team_members (
                                registration_id, player_name, player_email, player_phone, player_discord, 
                                player_student_id, player_department, player_semester, player_role, game_username
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $registration_id,
                            $member['player_name'],
                            $member['player_email'] ?? '',
                            $member['player_phone'] ?? '',
                            $member['player_discord'] ?? '',
                            $member['player_student_id'] ?? '',
                            $member['player_department'] ?? '',
                            $member['player_semester'] ?? '',
                            $member['player_role'] ?? 'member',
                            $member['game_username'] ?? ''
                        ]);
                    }
                }
            }
            
            // Update tournament participant count
            $stmt = $pdo->prepare("UPDATE tournaments SET current_participants = current_participants + 1 WHERE id = ?");
            $stmt->execute([$tournament_id]);
            
            $pdo->commit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
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
            FROM tournament_registrations r 
            JOIN tournaments t ON r.tournament_id = t.id 
            ORDER BY r.registration_date DESC
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
