<?php
/**
 * Image Serving Endpoint
 * Serves uploaded images through the API
 */

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get image path from URL
$requestUri = $_SERVER['REQUEST_URI'];
$pathInfo = parse_url($requestUri, PHP_URL_PATH);
$imagePath = str_replace('/api/images/', '', $pathInfo);

if (empty($imagePath)) {
    http_response_code(400);
    echo json_encode(['error' => 'Image path required']);
    exit;
}

// Load database configuration
require_once '../config/database.production.php';

try {
    // Create database connection
    $database = new Database();
    
    // Create image handler
    require_once 'handlers/images.php';
    $imageHandler = new ImageHandler($database);
    
    // Serve the image
    $imageHandler->serveImage($imagePath);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
