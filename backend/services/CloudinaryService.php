<?php
/**
 * Cloudinary Service for Image Management
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryService {
    private $cloudinary;
    private $uploadApi;
    
    public function __construct() {
        // Load from environment or fallback to empty/defaults
        // In production, these should be set in the environment
        if (!getenv('CLOUDINARY_URL') && isset($_ENV['CLOUDINARY_URL'])) {
            putenv("CLOUDINARY_URL={$_ENV['CLOUDINARY_URL']}");
        }
        
        // Debug: Log the values being used
        error_log("Cloudinary Config - Cloud Name: " . getenv('CLOUDINARY_CLOUD_NAME'));
        error_log("Cloudinary Config - API Key: " . getenv('CLOUDINARY_API_KEY'));
        error_log("Cloudinary Config - API Secret: " . substr(getenv('CLOUDINARY_API_SECRET'), 0, 5) . "...");
        
        // Configure Cloudinary using environment variables
        Configuration::instance([
            'cloud' => [
                'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME') ?: ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? ''),
                'api_key' => getenv('CLOUDINARY_API_KEY') ?: ($_ENV['CLOUDINARY_API_KEY'] ?? ''),
                'api_secret' => getenv('CLOUDINARY_API_SECRET') ?: ($_ENV['CLOUDINARY_API_SECRET'] ?? '')
            ],
            'url' => [
                'secure' => true
            ]
        ]);
        
        $this->cloudinary = new Cloudinary();
        $this->uploadApi = new UploadApi();
    }
    
    /**
     * Upload image to Cloudinary
     */
    public function uploadImage($filePath, $folder = 'diu-esports', $options = []) {
        try {
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'image',
                'quality' => 'auto',
                'fetch_format' => 'auto'
            ];
            
            $uploadOptions = array_merge($defaultOptions, $options);
            
            $result = $this->uploadApi->upload($filePath, $uploadOptions);
            
            return [
                'success' => true,
                'public_id' => $result['public_id'],
                'secure_url' => $result['secure_url'],
                'url' => $result['url'],
                'width' => $result['width'],
                'height' => $result['height'],
                'format' => $result['format'],
                'bytes' => $result['bytes']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload image from file upload
     */
    public function uploadFromFile($file, $folder = 'diu-esports', $options = []) {
        try {
            if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
                return [
                    'success' => false,
                    'error' => 'File not found'
                ];
            }
            
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'image',
                'quality' => 'auto',
                'fetch_format' => 'auto'
            ];
            
            $uploadOptions = array_merge($defaultOptions, $options);
            
            $result = $this->uploadApi->upload($file['tmp_name'], $uploadOptions);
            
            return [
                'success' => true,
                'public_id' => $result['public_id'],
                'secure_url' => $result['secure_url'],
                'url' => $result['url'],
                'width' => $result['width'],
                'height' => $result['height'],
                'format' => $result['format'],
                'bytes' => $result['bytes']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete image from Cloudinary
     */
    public function deleteImage($publicId) {
        try {
            $result = $this->uploadApi->destroy($publicId);
            
            return [
                'success' => true,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get optimized image URL
     */
    public function getOptimizedUrl($publicId, $transformations = []) {
        try {
            $defaultTransformations = [
                'quality' => 'auto',
                'fetch_format' => 'auto'
            ];
            
            $transformOptions = array_merge($defaultTransformations, $transformations);
            
            return $this->cloudinary->image($publicId)->resize($transformOptions)->toUrl();
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get image info
     */
    public function getImageInfo($publicId) {
        try {
            $result = $this->cloudinary->adminApi()->asset($publicId);
            
            return [
                'success' => true,
                'info' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
