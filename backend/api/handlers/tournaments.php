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
            // Temporarily remove the games table join to fix the API
            $sql = "SELECT t.*, 'Unknown Game' as game_name, 'Unknown' as genre 
                    FROM tournaments t 
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
            // Temporarily remove the games table join to fix the API
            $sql = "SELECT t.*, 'Unknown Game' as game_name, 'Unknown' as genre 
                    FROM tournaments t 
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
            $existing = $this->db->querySingle("SELECT id, poster_url FROM tournaments WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Tournament not found'
                ];
            }
            
            // Delete associated poster file
            if ($existing['poster_url']) {
                $this->deleteFile($existing['poster_url']);
            }
            
            // Delete tournament
            $sql = "DELETE FROM tournaments WHERE id = ?";
            $result = $this->db->execute($sql, [$id]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Tournament deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete tournament'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete tournament: ' . $e->getMessage()
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
            // Temporarily remove the games table join to fix the API
            $sql = "SELECT t.*, 'Unknown Game' as game_name, 'Unknown' as genre 
                    FROM tournaments t 
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
            // Temporarily remove the games table join to fix the API
            $sql = "SELECT t.*, 'Unknown Game' as game_name, 'Unknown' as genre 
                    FROM tournaments t 
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
