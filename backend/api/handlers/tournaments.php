<?php
/**
 * Tournaments API Handler
 * Manages all tournament-related operations
 */

class TournamentsHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all tournaments
     */
    public function getAll() {
        try {
            $sql = "SELECT t.*, g.name as game_name, g.genre 
                    FROM tournaments t 
                    LEFT JOIN games g ON t.game_id = g.id 
                    ORDER BY t.start_date DESC";
            $tournaments = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $tournaments,
                'message' => 'Tournaments retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve tournaments: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific tournament by ID
     */
    public function get($id) {
        try {
            $sql = "SELECT t.*, g.name as game_name, g.genre 
                    FROM tournaments t 
                    LEFT JOIN games g ON t.game_id = g.id 
                    WHERE t.id = ?";
            $tournament = $this->db->querySingle($sql, [$id]);
            
            if (!$tournament) {
                return [
                    'success' => false,
                    'message' => 'Tournament not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $tournament,
                'message' => 'Tournament retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve tournament: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new tournament
     */
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['game_id', 'name', 'start_date', 'end_date'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate dates
            if (strtotime($input['start_date']) >= strtotime($input['end_date'])) {
                return [
                    'success' => false,
                    'message' => 'End date must be after start date'
                ];
            }
            
            // Handle file upload for poster
            $poster_url = null;
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                $poster_url = $this->handleFileUpload($_FILES['poster'], 'posters');
            }
            
            $sql = "INSERT INTO tournaments (game_id, name, description, poster_url, start_date, end_date, prize_pool, max_participants, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $input['game_id'],
                $input['name'],
                $input['description'] ?? '',
                $poster_url,
                $input['start_date'],
                $input['end_date'],
                $input['prize_pool'] ?? null,
                $input['max_participants'] ?? null,
                $input['status'] ?? 'upcoming'
            ];
            
            $tournament_id = $this->db->execute($sql, $params);
            
            if ($tournament_id) {
                return [
                    'success' => true,
                    'data' => ['id' => $tournament_id],
                    'message' => 'Tournament created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create tournament'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create tournament: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing tournament
     */
    public function update($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if tournament exists
            $existing = $this->db->querySingle("SELECT id FROM tournaments WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Tournament not found'
                ];
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            $fields = ['game_id', 'name', 'description', 'start_date', 'end_date', 'prize_pool', 'max_participants', 'status'];
            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            // Handle file upload for poster
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                $poster_url = $this->handleFileUpload($_FILES['poster'], 'posters');
                $updates[] = "poster_url = ?";
                $params[] = $poster_url;
            }
            
            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $params[] = $id; // Add ID for WHERE clause
            
            $sql = "UPDATE tournaments SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Tournament updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update tournament'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update tournament: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a tournament
     */
    public function delete($id) {
        try {
            // Check if tournament exists
            $tournament = $this->get($id);
            if (!$tournament['success']) {
                return $tournament;
            }
            
            // Delete tournament
            $sql = "DELETE FROM tournaments WHERE id = ?";
            $this->db->execute($sql, [$id]);
            
            return [
                'success' => true,
                'message' => 'Tournament deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete tournament: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle POST actions
     */
    public function postAction($action) {
        switch ($action) {
            case 'register':
                return $this->register();
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }

    /**
     * Register for a tournament
     */
    public function register() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Debug: Log the received input
            error_log("Tournament registration received: " . print_r($input, true));
            
            // Validate required fields
            $required = ['tournament_id', 'team_name', 'team_type', 'captain_name', 'captain_email', 'captain_game_username'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate team type
            $validTypes = ['solo', 'duo', 'squad'];
            if (!in_array($input['team_type'], $validTypes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid team type'
                ];
            }
            
            // Validate team members based on team type
            $requiredMembers = $input['team_type'] === 'solo' ? 0 : 
                             ($input['team_type'] === 'duo' ? 1 : 3);
            
            if (isset($input['team_members']) && count($input['team_members']) !== $requiredMembers) {
                return [
                    'success' => false,
                    'message' => "Please add {$requiredMembers} team member(s) for {$input['team_type']} team"
                ];
            }
            
            // Validate team members have required fields
            if (isset($input['team_members']) && is_array($input['team_members'])) {
                foreach ($input['team_members'] as $member) {
                    if (empty($member['player_name']) || empty($member['game_username'])) {
                        return [
                            'success' => false,
                            'message' => 'All team members must have name and game username'
                        ];
                    }
                }
            }
            
            // Check if tournament exists and is open for registration
            $tournament = $this->get($input['tournament_id']);
            if (!$tournament['success']) {
                return [
                    'success' => false,
                    'message' => 'Tournament not found'
                ];
            }
            
            $tournamentData = $tournament['data'];
            if ($tournamentData['status'] !== 'upcoming') {
                return [
                    'success' => false,
                    'message' => 'Tournament is not open for registration'
                ];
            }
            
            // Check if tournament is full
            if ($tournamentData['max_participants'] && 
                $tournamentData['current_participants'] >= $tournamentData['max_participants']) {
                return [
                    'success' => false,
                    'message' => 'Tournament is full'
                ];
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Insert registration
                $sql = "INSERT INTO tournament_registrations 
                        (tournament_id, team_name, team_type, captain_name, captain_email, 
                         captain_phone, captain_discord, captain_student_id, captain_department, captain_semester) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $registrationId = $this->db->execute($sql, [
                    $input['tournament_id'],
                    $input['team_name'],
                    $input['team_type'],
                    $input['captain_name'],
                    $input['captain_email'],
                    $input['captain_phone'] ?? null,
                    $input['captain_discord'] ?? null,
                    $input['captain_student_id'] ?? null,
                    $input['captain_department'] ?? null,
                    $input['captain_semester'] ?? null
                ]);
                
                // Insert captain as first team member
                $sql = "INSERT INTO tournament_team_members 
                        (registration_id, player_name, player_email, player_phone, player_discord, 
                         player_student_id, player_department, player_semester, player_role, game_username) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'captain', ?)";
                
                $this->db->execute($sql, [
                    $registrationId,
                    $input['captain_name'],
                    $input['captain_email'],
                    $input['captain_phone'] ?? null,
                    $input['captain_discord'] ?? null,
                    $input['captain_student_id'] ?? null,
                    $input['captain_department'] ?? null,
                    $input['captain_semester'] ?? null,
                    $input['captain_game_username'] ?? '' // Game username is required
                ]);
                
                // Insert additional team members if provided
                if (isset($input['team_members']) && is_array($input['team_members'])) {
                    foreach ($input['team_members'] as $member) {
                        if (!empty($member['player_name']) && !empty($member['game_username'])) {
                            $sql = "INSERT INTO tournament_team_members 
                                    (registration_id, player_name, player_email, player_phone, player_discord, 
                                     player_student_id, player_department, player_semester, player_role, game_username) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            
                            $this->db->execute($sql, [
                                $registrationId,
                                $member['player_name'],
                                $member['player_email'] ?? null,
                                $member['player_phone'] ?? null,
                                $member['player_discord'] ?? null,
                                $member['player_student_id'] ?? null,
                                $member['player_department'] ?? null,
                                $member['player_semester'] ?? null,
                                $member['player_role'] ?? 'member',
                                $member['game_username'] ?? '' // Game username is required
                            ]);
                        }
                    }
                }
                
                // Update tournament participant count
                $sql = "UPDATE tournaments SET current_participants = current_participants + 1 WHERE id = ?";
                $this->db->execute($sql, [$input['tournament_id']]);
                
                $this->db->commit();
                
                return [
                    'success' => true,
                    'message' => 'Tournament registration successful',
                    'data' => ['registration_id' => $registrationId]
                ];
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to register for tournament: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get tournament registrations
     */
    public function getRegistrations($tournamentId) {
        try {
            $sql = "SELECT tr.*, 
                           t.name as tournament_name,
                           t.game_id,
                           g.name as game_name,
                           g.genre
                    FROM tournament_registrations tr
                    JOIN tournaments t ON tr.tournament_id = t.id
                    LEFT JOIN games g ON t.game_id = g.id
                    WHERE tr.tournament_id = ?
                    ORDER BY tr.registration_date DESC";
            
            $registrations = $this->db->queryAll($sql, [$tournamentId]);
            
            // Get team members for each registration
            foreach ($registrations as &$registration) {
                $sql = "SELECT * FROM tournament_team_members WHERE registration_id = ? ORDER BY player_role, id";
                $registration['team_members'] = $this->db->queryAll($sql, [$registration['id']]);
            }
            
            return [
                'success' => true,
                'data' => $registrations,
                'message' => 'Registrations retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve registrations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update registration status
     */
    public function updateRegistrationStatus($registrationId, $status) {
        try {
            $validStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status'
                ];
            }
            
            $sql = "UPDATE tournament_registrations SET status = ? WHERE id = ?";
            $this->db->execute($sql, [$status, $registrationId]);
            
            return [
                'success' => true,
                'message' => 'Registration status updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update registration status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all registrations across all tournaments
     */
    public function getAllRegistrations() {
        try {
            $sql = "SELECT tr.*, 
                           t.name as tournament_name,
                           t.game_id,
                           g.name as game_name,
                           g.genre
                    FROM tournament_registrations tr
                    JOIN tournaments t ON tr.tournament_id = t.id
                    LEFT JOIN games g ON t.game_id = g.id
                    ORDER BY tr.registration_date DESC";
            
            $registrations = $this->db->queryAll($sql);
            
            // Get team members for each registration
            foreach ($registrations as &$registration) {
                $sql = "SELECT * FROM tournament_team_members WHERE registration_id = ? ORDER BY player_role, id";
                $registration['team_members'] = $this->db->queryAll($sql, [$registration['id']]);
            }
            
            return [
                'success' => true,
                'data' => $registrations,
                'message' => 'All registrations retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve registrations: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get tournaments by status
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'upcoming':
                return $this->getByStatus('upcoming');
            case 'ongoing':
                return $this->getByStatus('ongoing');
            case 'completed':
                return $this->getByStatus('completed');
            case 'by-game':
                return $this->getByGame($id);
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Get tournaments by status
     */
    private function getByStatus($status) {
        try {
            $sql = "SELECT t.*, g.name as game_name, g.genre 
                    FROM tournaments t 
                    LEFT JOIN games g ON t.game_id = g.id 
                    WHERE t.status = ? 
                    ORDER BY t.start_date ASC";
            $tournaments = $this->db->queryAll($sql, [$status]);
            
            return [
                'success' => true,
                'data' => $tournaments,
                'message' => ucfirst($status) . ' tournaments retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve tournaments: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get tournaments by game
     */
    private function getByGame($game_id) {
        try {
            $sql = "SELECT t.*, g.name as game_name, g.genre 
                    FROM tournaments t 
                    LEFT JOIN games g ON t.game_id = g.id 
                    WHERE t.game_id = ? 
                    ORDER BY t.start_date DESC";
            $tournaments = $this->db->queryAll($sql, [$game_id]);
            
            return [
                'success' => true,
                'data' => $tournaments,
                'message' => 'Tournaments for game retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve tournaments: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file, $directory) {
        $upload_dir = "../uploads/$directory/";
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
        }
        
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to upload file.');
        }
        
        return "uploads/$directory/" . $filename;
    }
    
    /**
     * Delete file
     */
    private function deleteFile($filepath) {
        $full_path = "../" . $filepath;
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
}
?>
