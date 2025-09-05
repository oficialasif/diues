<?php
/**
 * Simple Cloudinary Service (without Composer autoload)
 * This is a fallback version that works without Composer
 */

class CloudinaryServiceSimple {
    private $cloudName;
    private $apiKey;
    private $apiSecret;
    
    public function __construct() {
        // Get environment variables (try both $_ENV and getenv)
        $this->cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? getenv('CLOUDINARY_CLOUD_NAME') ?? 'dn7ucxk8a';
        $this->apiKey = $_ENV['CLOUDINARY_API_KEY'] ?? getenv('CLOUDINARY_API_KEY') ?? 'oNE1GqwM-WYb_REcNFr39eqwCY0';
        $this->apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? getenv('CLOUDINARY_API_SECRET') ?? '246184425446679';
        
        // Force the correct values for now
        $this->cloudName = 'dn7ucxk8a';
        $this->apiKey = 'oNE1GqwM-WYb_REcNFr39eqwCY0';
        $this->apiSecret = '246184425446679';
    }
    
    /**
     * Upload image using Cloudinary REST API
     */
    public function uploadFromFile($file, $folder = 'diu-esports') {
        try {
            if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
                return [
                    'success' => false,
                    'error' => 'File not found'
                ];
            }
            
            // Prepare upload data
            $postData = [
                'file' => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
                'upload_preset' => 'ml_default', // You need to create this preset in Cloudinary
                'folder' => $folder,
                'quality' => 'auto',
                'fetch_format' => 'auto'
            ];
            
            // Upload to Cloudinary
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.cloudinary.com/v1_1/' . $this->cloudName . '/image/upload');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $result = json_decode($response, true);
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
            } else {
                return [
                    'success' => false,
                    'error' => 'Upload failed: HTTP ' . $httpCode . ' - ' . $response
                ];
            }
            
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
    public function getOptimizedUrl($publicId, $width = null, $height = null) {
        $baseUrl = 'https://res.cloudinary.com/' . $this->cloudName . '/image/upload/';
        
        $transformations = [];
        if ($width) $transformations[] = 'w_' . $width;
        if ($height) $transformations[] = 'h_' . $height;
        $transformations[] = 'q_auto,f_auto';
        
        $transformString = implode(',', $transformations);
        
        return $baseUrl . $transformString . '/' . $publicId;
    }
}
?>
