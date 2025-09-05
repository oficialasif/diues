<?php
/**
 * Test Cloudinary Configuration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load Cloudinary SDK if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

try {
    // Set environment variables
    putenv('CLOUDINARY_CLOUD_NAME=dn7ucxk8a');
    putenv('CLOUDINARY_API_KEY=oNE1GqwM-WYb_REcNFr39eqwCY0');
    putenv('CLOUDINARY_API_SECRET=246184425446679');
    
    $_ENV['CLOUDINARY_CLOUD_NAME'] = 'dn7ucxk8a';
    $_ENV['CLOUDINARY_API_KEY'] = 'oNE1GqwM-WYb_REcNFr39eqwCY0';
    $_ENV['CLOUDINARY_API_SECRET'] = '246184425446679';
    
    // Test if Cloudinary SDK can be loaded
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        
            // Test configuration - Set environment variables first
    $_ENV['CLOUDINARY_URL'] = 'cloudinary://oNE1GqwM-WYb_REcNFr39eqwCY0:246184425446679@dn7ucxk8a';
    putenv('CLOUDINARY_URL=cloudinary://oNE1GqwM-WYb_REcNFr39eqwCY0:246184425446679@dn7ucxk8a');
    
    // Test configuration
    Configuration::instance([
        'cloud' => [
            'cloud_name' => 'dn7ucxk8a',
            'api_key' => 'oNE1GqwM-WYb_REcNFr39eqwCY0',
            'api_secret' => '246184425446679'
        ],
        'url' => [
            'secure' => true
        ]
    ]);
    
    $cloudinary = new Cloudinary();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cloudinary configuration successful',
            'cloud_name' => 'dn7ucxk8a',
            'sdk_loaded' => true
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Composer autoload not found',
            'sdk_loaded' => false
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
