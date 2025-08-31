<?php
/**
 * File Storage Service for DIU Esports Community
 * Handles file uploads with support for local and cloud storage
 */

class FileStorage {
    private $storageType;
    private $uploadPath;
    private $allowedTypes;
    private $maxFileSize;
    private $cloudConfig;
    
    public function __construct() {
        $this->storageType = $_ENV['STORAGE_TYPE'] ?? 'local';
        $this->uploadPath = $_ENV['UPLOAD_PATH'] ?? 'uploads/';
        $this->allowedTypes = explode(',', $_ENV['ALLOWED_FILE_TYPES'] ?? 'image/jpeg,image/png,image/gif,image/webp');
        $this->maxFileSize = $_ENV['MAX_FILE_SIZE'] ?? 5242880; // 5MB default
        
        // Cloud storage configuration
        $this->cloudConfig = [
            'aws' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '',
                'region' => $_ENV['AWS_DEFAULT_REGION'] ?? 'us-east-1',
                'bucket' => $_ENV['AWS_S3_BUCKET'] ?? ''
            ],
            'cloudinary' => [
                'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '',
                'api_key' => $_ENV['CLOUDINARY_API_KEY'] ?? '',
                'api_secret' => $_ENV['CLOUDINARY_API_SECRET'] ?? ''
            ]
        ];
    }
    
    /**
     * Upload a file
     */
    public function uploadFile($file, $category = 'general') {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name']);
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            
            // Create category directory
            $categoryPath = $this->uploadPath . $category . '/';
            if (!is_dir($categoryPath)) {
                mkdir($categoryPath, 0755, true);
            }
            
            // Handle different storage types
            switch ($this->storageType) {
                case 'aws':
                    return $this->uploadToAWS($file, $filename, $category);
                case 'cloudinary':
                    return $this->uploadToCloudinary($file, $filename, $category);
                case 'local':
                default:
                    return $this->uploadToLocal($file, $filename, $categoryPath);
            }
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'File upload failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload to local storage
     */
    private function uploadToLocal($file, $filename, $categoryPath) {
        $filepath = $categoryPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => $this->getFileUrl($filepath),
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to move uploaded file'
        ];
    }
    
    /**
     * Upload to AWS S3
     */
    private function uploadToAWS($file, $filename, $category) {
        if (empty($this->cloudConfig['aws']['key'])) {
            return [
                'success' => false,
                'message' => 'AWS S3 not configured'
            ];
        }
        
        // This would require AWS SDK - simplified for now
        // In production, you'd use AWS SDK for PHP
        return [
            'success' => false,
            'message' => 'AWS S3 upload not implemented yet'
        ];
    }
    
    /**
     * Upload to Cloudinary
     */
    private function uploadToCloudinary($file, $filename, $category) {
        if (empty($this->cloudConfig['cloudinary']['cloud_name'])) {
            return [
                'success' => false,
                'message' => 'Cloudinary not configured'
            ];
        }
        
        // This would require Cloudinary SDK - simplified for now
        // In production, you'd use Cloudinary PHP SDK
        return [
            'success' => false,
            'message' => 'Cloudinary upload not implemented yet'
        ];
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'message' => 'File size exceeds limit (' . ($this->maxFileSize / 1024 / 1024) . 'MB)'
            ];
        }
        
        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            return [
                'success' => false,
                'message' => 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes)
            ];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'File upload error: ' . $this->getUploadErrorMessage($file['error'])
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Get file URL
     */
    private function getFileUrl($filepath) {
        if ($this->storageType === 'local') {
            // For local storage, return relative path
            return '/' . $filepath;
        }
        
        // For cloud storage, return full URL
        return $filepath;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Delete file
     */
    public function deleteFile($filepath) {
        try {
            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    return ['success' => true];
                }
            }
            
            return [
                'success' => false,
                'message' => 'File not found or could not be deleted'
            ];
            
        } catch (Exception $e) {
            error_log("File deletion error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'File deletion failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get storage information
     */
    public function getStorageInfo() {
        return [
            'type' => $this->storageType,
            'upload_path' => $this->uploadPath,
            'allowed_types' => $this->allowedTypes,
            'max_file_size' => $this->maxFileSize,
            'cloud_configured' => !empty($this->cloudConfig['aws']['key']) || !empty($this->cloudConfig['cloudinary']['cloud_name'])
        ];
    }
}
?>
