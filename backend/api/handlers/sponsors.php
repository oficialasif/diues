<?php
/**
 * Sponsors API Handler
 * Manages all sponsor-related operations
 */

class SponsorsHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all sponsors
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM sponsors ORDER BY partnership_type ASC, name ASC";
            $sponsors = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $sponsors,
                'message' => 'Sponsors retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve sponsors: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific sponsor by ID
     */
    public function get($id) {
        try {
            $sql = "SELECT * FROM sponsors WHERE id = ?";
            $sponsor = $this->db->querySingle($sql, [$id]);
            
            if (!$sponsor) {
                return [
                    'success' => false,
                    'message' => 'Sponsor not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $sponsor,
                'message' => 'Sponsor retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve sponsor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new sponsor
     */
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['name', 'partnership_type'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate partnership type
            $valid_types = ['platinum', 'gold', 'silver', 'bronze'];
            if (!in_array($input['partnership_type'], $valid_types)) {
                return [
                    'success' => false,
                    'message' => 'Invalid partnership type. Must be one of: ' . implode(', ', $valid_types)
                ];
            }
            
            // Handle file upload for logo
            $logo_url = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logo_url = $this->handleFileUpload($_FILES['logo'], 'logos');
            }
            
            $sql = "INSERT INTO sponsors (name, logo_url, category, partnership_type, website_url, benefits, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $input['name'],
                $logo_url,
                $input['category'] ?? '',
                $input['partnership_type'],
                $input['website_url'] ?? '',
                $input['benefits'] ?? '',
                $input['is_active'] ?? true
            ];
            
            $sponsor_id = $this->db->execute($sql, $params);
            
            if ($sponsor_id) {
                return [
                    'success' => true,
                    'data' => ['id' => $sponsor_id],
                    'message' => 'Sponsor created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create sponsor'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create sponsor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing sponsor
     */
    public function update($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if sponsor exists
            $existing = $this->db->querySingle("SELECT id FROM sponsors WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Sponsor not found'
                ];
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            $fields = ['name', 'category', 'partnership_type', 'website_url', 'benefits', 'is_active'];
            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            // Handle file upload for logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logo_url = $this->handleFileUpload($_FILES['logo'], 'logos');
                $updates[] = "logo_url = ?";
                $params[] = $logo_url;
            }
            
            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $params[] = $id; // Add ID for WHERE clause
            
            $sql = "UPDATE sponsors SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Sponsor updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update sponsor'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update sponsor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a sponsor
     */
    public function delete($id) {
        try {
            // Check if sponsor exists
            $existing = $this->db->querySingle("SELECT id, logo_url FROM sponsors WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Sponsor not found'
                ];
            }
            
            // Delete associated logo file
            if ($existing['logo_url']) {
                $this->deleteFile($existing['logo_url']);
            }
            
            // Delete sponsor
            $sql = "DELETE FROM sponsors WHERE id = ?";
            $result = $this->db->execute($sql, [$id]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Sponsor deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete sponsor'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete sponsor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get sponsors by action
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'active':
                return $this->getActive();
            case 'by-tier':
                return $this->getByTier($id);
            case 'by-category':
                return $this->getByCategory($id);
            case 'platinum':
                return $this->getByTier('platinum');
            case 'gold':
                return $this->getByTier('gold');
            case 'silver':
                return $this->getByTier('silver');
            case 'bronze':
                return $this->getByTier('bronze');
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Get active sponsors
     */
    private function getActive() {
        try {
            $sql = "SELECT * FROM sponsors WHERE is_active = 1 ORDER BY partnership_type ASC, name ASC";
            $sponsors = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $sponsors,
                'message' => 'Active sponsors retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve active sponsors: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get sponsors by partnership tier
     */
    private function getByTier($tier) {
        try {
            $sql = "SELECT * FROM sponsors WHERE partnership_type = ? AND is_active = 1 ORDER BY name ASC";
            $sponsors = $this->db->queryAll($sql, [$tier]);
            
            return [
                'success' => true,
                'data' => $sponsors,
                'message' => ucfirst($tier) . ' tier sponsors retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve sponsors: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get sponsors by category
     */
    private function getByCategory($category) {
        try {
            $sql = "SELECT * FROM sponsors WHERE category = ? AND is_active = 1 ORDER BY partnership_type ASC, name ASC";
            $sponsors = $this->db->queryAll($sql, [$category]);
            
            return [
                'success' => true,
                'data' => $sponsors,
                'message' => 'Sponsors for category retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve sponsors: ' . $e->getMessage()
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
