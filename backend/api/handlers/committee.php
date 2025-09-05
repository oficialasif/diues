<?php
/**
 * Committee API Handler
 * Manages all committee member-related operations
 */

class CommitteeHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all committee members
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM committee_members ORDER BY year DESC, is_current DESC, name ASC";
            $members = $this->db->queryAll($sql);
            
            // Process image URLs
            require_once 'images.php';
            $imageHandler = new ImageHandler($this->db);
            $members = $imageHandler->processImageUrls($members);
            
            return [
                'success' => true,
                'data' => $members,
                'message' => 'Committee members retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve committee members: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific committee member by ID
     */
    public function get($id) {
        try {
            $sql = "SELECT * FROM committee_members WHERE id = ?";
            $member = $this->db->querySingle($sql, [$id]);
            
            if (!$member) {
                return [
                    'success' => false,
                    'message' => 'Committee member not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $member,
                'message' => 'Committee member retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve committee member: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new committee member
     */
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['name', 'role', 'position', 'year'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Handle file upload for image
            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_url = $this->handleFileUpload($_FILES['image'], 'photos');
            }
            
            // Parse social links if provided
            $social_links = null;
            if (isset($input['social_links']) && is_array($input['social_links'])) {
                $social_links = json_encode($input['social_links']);
            }
            
            $sql = "INSERT INTO committee_members (name, role, position, image_url, bio, achievements, social_links, is_current, year) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $input['name'],
                $input['role'],
                $input['position'],
                $image_url,
                $input['bio'] ?? '',
                $input['achievements'] ?? '',
                $social_links,
                $input['is_current'] ?? true,
                $input['year']
            ];
            
            $member_id = $this->db->execute($sql, $params);
            
            if ($member_id) {
                return [
                    'success' => true,
                    'data' => ['id' => $member_id],
                    'message' => 'Committee member created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create committee member'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create committee member: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing committee member
     */
    public function update($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if member exists
            $existing = $this->db->querySingle("SELECT id FROM committee_members WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Committee member not found'
                ];
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            $fields = ['name', 'role', 'position', 'bio', 'achievements', 'is_current', 'year'];
            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            // Handle social links
            if (isset($input['social_links']) && is_array($input['social_links'])) {
                $updates[] = "social_links = ?";
                $params[] = json_encode($input['social_links']);
            }
            
            // Handle file upload for image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_url = $this->handleFileUpload($_FILES['image'], 'photos');
                $updates[] = "image_url = ?";
                $params[] = $image_url;
            }
            
            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $params[] = $id; // Add ID for WHERE clause
            
            $sql = "UPDATE committee_members SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Committee member updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update committee member'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update committee member: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a committee member
     */
    public function delete($id) {
        try {
            // Check if member exists
            $existing = $this->db->querySingle("SELECT id, image_url FROM committee_members WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Committee member not found'
                ];
            }
            
            // Delete associated image file
            if ($existing['image_url']) {
                $this->deleteFile($existing['image_url']);
            }
            
            // Delete member
            $sql = "DELETE FROM committee_members WHERE id = ?";
            $result = $this->db->execute($sql, [$id]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Committee member deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete committee member'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete committee member: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get committee members by action
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'current':
                return $this->getCurrent();
            case 'past':
                return $this->getPast();
            case 'by-year':
                return $this->getByYear($id);
            case 'by-role':
                return $this->getByRole($id);
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Get current committee members
     */
    private function getCurrent() {
        try {
            $sql = "SELECT * FROM committee_members WHERE is_current = 1 ORDER BY name ASC";
            $members = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $members,
                'message' => 'Current committee members retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve current committee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get past committee members
     */
    private function getPast() {
        try {
            $sql = "SELECT * FROM committee_members WHERE is_current = 0 ORDER BY year DESC, name ASC";
            $members = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $members,
                'message' => 'Past committee members retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve past committee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get committee members by year
     */
    private function getByYear($year) {
        try {
            $sql = "SELECT * FROM committee_members WHERE year = ? ORDER BY name ASC";
            $members = $this->db->queryAll($sql, [$year]);
            
            return [
                'success' => true,
                'data' => $members,
                'message' => 'Committee members for year retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve committee members: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get committee members by role
     */
    private function getByRole($role) {
        try {
            $sql = "SELECT * FROM committee_members WHERE role = ? ORDER BY year DESC, name ASC";
            $members = $this->db->queryAll($sql, [$role]);
            
            return [
                'success' => true,
                'data' => $members,
                'message' => 'Committee members for role retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve committee members: ' . $e->getMessage()
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
