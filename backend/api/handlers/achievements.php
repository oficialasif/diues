<?php
/**
 * Achievements API Handler
 * Manages all achievement-related operations
 */

class AchievementsHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all achievements
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM achievements ORDER BY year DESC, category ASC, title ASC";
            $achievements = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $achievements,
                'message' => 'Achievements retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve achievements: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific achievement by ID
     */
    public function get($id) {
        try {
            $sql = "SELECT * FROM achievements WHERE id = ?";
            $achievement = $this->db->querySingle($sql, [$id]);
            
            if (!$achievement) {
                return [
                    'success' => false,
                    'message' => 'Achievement not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $achievement,
                'message' => 'Achievement retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve achievement: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new achievement
     */
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['title', 'category', 'year'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate category
            $valid_categories = ['tournament', 'individual', 'team', 'community'];
            if (!in_array($input['category'], $valid_categories)) {
                return [
                    'success' => false,
                    'message' => 'Invalid category. Must be one of: ' . implode(', ', $valid_categories)
                ];
            }
            
            // Handle file upload for icon
            $icon_url = null;
            if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
                $icon_url = $this->handleFileUpload($_FILES['icon'], 'icons');
            }
            
            $sql = "INSERT INTO achievements (title, description, category, year, icon_url, highlights_url) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $params = [
                $input['title'],
                $input['description'] ?? '',
                $input['category'],
                $input['year'],
                $icon_url,
                $input['highlights_url'] ?? ''
            ];
            
            $achievement_id = $this->db->execute($sql, $params);
            
            if ($achievement_id) {
                return [
                    'success' => true,
                    'data' => ['id' => $achievement_id],
                    'message' => 'Achievement created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create achievement'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create achievement: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing achievement
     */
    public function update($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if achievement exists
            $existing = $this->db->querySingle("SELECT id FROM achievements WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Achievement not found'
                ];
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            $fields = ['title', 'description', 'category', 'year', 'highlights_url'];
            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            // Handle file upload for icon
            if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
                $icon_url = $this->handleFileUpload($_FILES['icon'], 'icons');
                $updates[] = "icon_url = ?";
                $params[] = $icon_url;
            }
            
            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $params[] = $id; // Add ID for WHERE clause
            
            $sql = "UPDATE achievements SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Achievement updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update achievement'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update achievement: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete an achievement
     */
    public function delete($id) {
        try {
            // Check if achievement exists
            $existing = $this->db->querySingle("SELECT id, icon_url FROM achievements WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Achievement not found'
                ];
            }
            
            // Delete associated icon file
            if ($existing['icon_url']) {
                $this->deleteFile($existing['icon_url']);
            }
            
            // Delete achievement
            $sql = "DELETE FROM achievements WHERE id = ?";
            $result = $this->db->execute($sql, [$id]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Achievement deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete achievement'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete achievement: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get achievements by action
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'by-category':
                return $this->getByCategory($id);
            case 'by-year':
                return $this->getByYear($id);
            case 'tournament':
                return $this->getByCategory('tournament');
            case 'individual':
                return $this->getByCategory('individual');
            case 'team':
                return $this->getByCategory('team');
            case 'community':
                return $this->getByCategory('community');
            case 'recent':
                return $this->getRecent();
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Get achievements by category
     */
    private function getByCategory($category) {
        try {
            $sql = "SELECT * FROM achievements WHERE category = ? ORDER BY year DESC, title ASC";
            $achievements = $this->db->queryAll($sql, [$category]);
            
            return [
                'success' => true,
                'data' => $achievements,
                'message' => ucfirst($category) . ' achievements retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve achievements: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get achievements by year
     */
    private function getByYear($year) {
        try {
            $sql = "SELECT * FROM achievements WHERE year = ? ORDER BY category ASC, title ASC";
            $achievements = $this->db->queryAll($sql, [$year]);
            
            return [
                'success' => true,
                'data' => $achievements,
                'message' => 'Achievements for year retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve achievements: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get recent achievements
     */
    private function getRecent() {
        try {
            $sql = "SELECT * FROM achievements ORDER BY year DESC, created_at DESC LIMIT 10";
            $achievements = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $achievements,
                'message' => 'Recent achievements retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve recent achievements: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file, $directory) {
        $upload_dir = "../uploads/$directory/";
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, WebP, and SVG are allowed.');
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
