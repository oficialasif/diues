<?php
/**
 * Image Proxy Handler
 * Serves uploaded images through the API
 */

class ImageHandler {
    private $db;
    private $baseUrl;
    
    public function __construct($database) {
        $this->db = $database;
        // Use the backend URL for image serving
        $this->baseUrl = rtrim($_ENV['BACKEND_URL'] ?? 'https://diu-esports-backend.onrender.com', '/');
    }
    
    /**
     * Get full image URL for a relative path
     */
    public function getImageUrl($relativePath) {
        if (empty($relativePath)) {
            return null;
        }
        
        // If it's already a full URL, return as is
        if (filter_var($relativePath, FILTER_VALIDATE_URL)) {
            return $relativePath;
        }
        
        // Convert relative path to full URL
        $relativePath = ltrim($relativePath, '/');
        return $this->baseUrl . '/api/images/' . $relativePath;
    }
    
    /**
     * Serve image file
     */
    public function serveImage($imagePath) {
        try {
            // Security: Only allow images from uploads directory
            $allowedDirs = ['uploads/photos/', 'uploads/logos/', 'uploads/posters/', 'uploads/icons/'];
            $isAllowed = false;
            
            // Normalize the path
            $normalizedPath = ltrim($imagePath, '/');
            
            foreach ($allowedDirs as $dir) {
                if (strpos($normalizedPath, $dir) === 0) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied', 'debug' => [
                    'image_path' => $imagePath,
                    'normalized_path' => $normalizedPath,
                    'allowed_dirs' => $allowedDirs
                ]]);
                return;
            }
            
            $fullPath = '../' . $imagePath;
            
            if (!file_exists($fullPath)) {
                http_response_code(404);
                echo json_encode(['error' => 'Image not found']);
                return;
            }
            
            // Get file info
            $fileInfo = pathinfo($fullPath);
            $mimeType = $this->getMimeType($fileInfo['extension']);
            
            // Set headers
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($fullPath));
            header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            
            // Output file
            readfile($fullPath);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to serve image: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get MIME type based on file extension
     */
    private function getMimeType($extension) {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml'
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
    
    /**
     * Process image URLs in data array
     */
    public function processImageUrls($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->processImageUrls($value);
                } elseif (in_array($key, ['image_url', 'poster_url', 'logo_url', 'icon_url']) && !empty($value)) {
                    $data[$key] = $this->getImageUrl($value);
                }
            }
        }
        
        return $data;
    }
}
