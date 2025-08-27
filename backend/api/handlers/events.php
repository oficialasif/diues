<?php
/**
 * Events API Handler
 * Manages all event-related operations
 */

class EventsHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all events
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM events ORDER BY event_date DESC";
            $events = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve events: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific event by ID
     */
    public function get($id) {
        try {
            $sql = "SELECT * FROM events WHERE id = ?";
            $event = $this->db->querySingle($sql, [$id]);
            
            if (!$event) {
                return [
                    'success' => false,
                    'message' => 'Event not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $event,
                'message' => 'Event retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve event: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new event
     */
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['title', 'event_date', 'event_type'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate event date
            if (strtotime($input['event_date']) < time()) {
                return [
                    'success' => false,
                    'message' => 'Event date cannot be in the past'
                ];
            }
            
            // Handle file upload for poster
            $poster_url = null;
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                $poster_url = $this->handleFileUpload($_FILES['poster'], 'posters');
            }
            
            $sql = "INSERT INTO events (title, description, poster_url, event_date, location, event_type, is_featured, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $input['title'],
                $input['description'] ?? '',
                $poster_url,
                $input['event_date'],
                $input['location'] ?? '',
                $input['event_type'],
                $input['is_featured'] ?? false,
                $input['status'] ?? 'upcoming'
            ];
            
            $event_id = $this->db->execute($sql, $params);
            
            if ($event_id) {
                return [
                    'success' => true,
                    'data' => ['id' => $event_id],
                    'message' => 'Event created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create event'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing event
     */
    public function update($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if event exists
            $existing = $this->db->querySingle("SELECT id FROM events WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Event not found'
                ];
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            $fields = ['title', 'description', 'event_date', 'location', 'event_type', 'is_featured', 'status'];
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
            
            $sql = "UPDATE events SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Event updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update event'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete an event
     */
    public function delete($id) {
        try {
            // Check if event exists
            $existing = $this->db->querySingle("SELECT id, poster_url FROM events WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Event not found'
                ];
            }
            
            // Delete associated poster file
            if ($existing['poster_url']) {
                $this->deleteFile($existing['poster_url']);
            }
            
            // Delete event
            $sql = "DELETE FROM events WHERE id = ?";
            $result = $this->db->execute($sql, [$id]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Event deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete event'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get events by action
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'upcoming':
                return $this->getByStatus('upcoming');
            case 'ongoing':
                return $this->getByStatus('ongoing');
            case 'completed':
                return $this->getByStatus('completed');
            case 'featured':
                return $this->getFeatured();
            case 'by-type':
                return $this->getByType($id);
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Get events by status
     */
    private function getByStatus($status) {
        try {
            $sql = "SELECT * FROM events WHERE status = ? ORDER BY event_date ASC";
            $events = $this->db->queryAll($sql, [$status]);
            
            return [
                'success' => true,
                'data' => $events,
                'message' => ucfirst($status) . ' events retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve events: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get featured events
     */
    private function getFeatured() {
        try {
            $sql = "SELECT * FROM events WHERE is_featured = 1 AND status = 'upcoming' ORDER BY event_date ASC";
            $events = $this->db->queryAll($sql);
            
            return [
                'success' => true,
                'data' => $events,
                'message' => 'Featured events retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve featured events: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get events by type
     */
    private function getByType($type) {
        try {
            $sql = "SELECT * FROM events WHERE event_type = ? ORDER BY event_date DESC";
            $events = $this->db->queryAll($sql, [$type]);
            
            return [
                'success' => true,
                'data' => $events,
                'message' => 'Events for type retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve events: ' . $e->getMessage()
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
