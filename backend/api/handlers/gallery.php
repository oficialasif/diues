<?php
/**
 * Gallery API Handler
 * Manages all gallery item-related operations
 */

class GalleryHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all gallery items
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM gallery ORDER BY year DESC, is_featured DESC, created_at DESC";
            $items = $this->db->queryAll($sql);
            
            // Process image URLs
            require_once 'images.php';
            $imageHandler = new ImageHandler($this->db);
            $items = $imageHandler->processImageUrls($items);
            
            return [
                'success' => true,
                'data' => $items,
                'message' => 'Gallery items retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve gallery items: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific gallery item by ID
     */
    public function get($id) {
        try {
            $sql = "SELECT * FROM gallery WHERE id = ?";
            $item = $this->db->querySingle($sql, [$id]);
            
            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Gallery item not found'
                ];
            }
            
            // Process image URLs
            require_once 'images.php';
            $imageHandler = new ImageHandler($this->db);
            $item = $imageHandler->processImageUrls($item);
            
            return [
                'success' => true,
                'data' => $item,
                'message' => 'Gallery item retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve gallery item: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new gallery item
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
            
            // Handle file uploads
            $image_url = null;
            $video_url = null;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_url = $this->handleFileUpload($_FILES['image'], 'photos');
            }
            
            if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
                $video_url = $this->handleVideoUpload($_FILES['video'], 'highlights');
            }
            
            // Parse tags if provided
            $tags = null;
            if (isset($input['tags']) && is_array($input['tags'])) {
                $tags = json_encode($input['tags']);
            }
            
            $sql = "INSERT INTO gallery (title, description, image_url, video_url, category, year, tags, is_featured) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $input['title'],
                $input['description'] ?? '',
                $image_url,
                $video_url,
                $input['category'],
                $input['year'],
                $tags,
                $input['is_featured'] ?? false
            ];
            
            $item_id = $this->db->execute($sql, $params);
            
            if ($item_id) {
                return [
                    'success' => true,
                    'data' => ['id' => $item_id],
                    'message' => 'Gallery item created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create gallery item'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create gallery item: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing gallery item
     */
    public function update($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if item exists
            $existing = $this->db->querySingle("SELECT id FROM gallery WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Gallery item not found'
                ];
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            $fields = ['title', 'description', 'category', 'year', 'is_featured'];
            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            // Handle tags
            if (isset($input['tags']) && is_array($input['tags'])) {
                $updates[] = "tags = ?";
                $params[] = json_encode($input['tags']);
            }
            
            // Handle file uploads
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_url = $this->handleFileUpload($_FILES['image'], 'photos');
                $updates[] = "image_url = ?";
                $params[] = $image_url;
            }
            
            if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
                $video_url = $this->handleVideoUpload($_FILES['video'], 'highlights');
                $updates[] = "video_url = ?";
                $params[] = $video_url;
            }
            
            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $params[] = $id; // Add ID for WHERE clause
            
            $sql = "UPDATE gallery SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Gallery item updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update gallery item'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update gallery item: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a gallery item
     */
    public function delete($id) {
        try {
            // Check if item exists
            $existing = $this->db->querySingle("SELECT id, image_url, video_url FROM gallery WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Gallery item not found'
                ];
            }
            
            // Delete associated files
            if ($existing['image_url']) {
                $this->deleteFile($existing['image_url']);
            }
            
            if ($existing['video_url']) {
                $this->deleteFile($existing['video_url']);
            }
            
            // Delete item
            $sql = "DELETE FROM gallery WHERE id = ?";
            $result = $this->db->execute($sql, [$id]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Gallery item deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete gallery item'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete gallery item: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get gallery items by action
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'featured':
                return $this->getFeatured();
            case 'by-category':
                return $this->getByCategory($id);
            case 'by-year':
                return $this->getByYear($id);
            case 'images':
                return $this->getImages();
            case 'videos':
                return $this->getVideos();
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Get featured gallery items
     */
    private function getFeatured() {
        try {
            $sql = "SELECT * FROM gallery WHERE is_featured = 1 ORDER BY year DESC, created_at DESC";
            $items = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $items,
                'message' => 'Featured gallery items retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve featured items: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get gallery items by category
     */
    private function getByCategory($category) {
        try {
            $sql = "SELECT * FROM gallery WHERE category = ? ORDER BY year DESC, created_at DESC";
            $items = $this->db->queryAll($sql, [$category]);
            
            return [
                'success' => true,
                'data' => $items,
                'message' => 'Gallery items for category retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve gallery items: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get gallery items by year
     */
    private function getByYear($year) {
        try {
            $sql = "SELECT * FROM gallery WHERE year = ? ORDER BY created_at DESC";
            $items = $this->db->queryAll($sql, [$year]);
            
            return [
                'success' => true,
                'data' => $items,
                'message' => 'Gallery items for year retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve gallery items: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get image-only gallery items
     */
    private function getImages() {
        try {
            $sql = "SELECT * FROM gallery WHERE image_url IS NOT NULL ORDER BY year DESC, created_at DESC";
            $items = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $items,
                'message' => 'Image gallery items retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve image items: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get video-only gallery items
     */
    private function getVideos() {
        try {
            $sql = "SELECT * FROM gallery WHERE video_url IS NOT NULL ORDER BY year DESC, created_at DESC";
            $items = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $items,
                'message' => 'Video gallery items retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve video items: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle image file upload
     */
    private function handleFileUpload($file, $directory) {
        $upload_dir = "../uploads/$directory/";
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Invalid image file type. Only JPG, PNG, GIF, and WebP are allowed.');
        }
        
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to upload image file.');
        }
        
        return "uploads/$directory/" . $filename;
    }
    
    /**
     * Handle video file upload
     */
    private function handleVideoUpload($file, $directory) {
        $upload_dir = "../uploads/$directory/";
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Invalid video file type. Only MP4, AVI, MOV, WMV, FLV, and WebM are allowed.');
        }
        
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to upload video file.');
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
