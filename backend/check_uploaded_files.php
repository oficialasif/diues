<?php
/**
 * Check what files are actually in the database vs filesystem
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Load production config
    require_once __DIR__ . '/config/config.production.php';
    require_once __DIR__ . '/config/database.production.php';
    
    $database = new Database();
    
    // Get all image URLs from database
    $gallery = $database->queryAll("SELECT id, title, image_url FROM gallery");
    $events = $database->queryAll("SELECT id, title, poster_url FROM events");
    $committee = $database->queryAll("SELECT id, name, image_url FROM committee_members");
    $sponsors = $database->queryAll("SELECT id, name, logo_url FROM sponsors");
    $tournaments = $database->queryAll("SELECT id, name, poster_url FROM tournaments");
    
    // Check which files exist
    $checkFile = function($path) {
        $fullPath = '../' . $path;
        return [
            'path' => $path,
            'exists' => file_exists($fullPath),
            'size' => file_exists($fullPath) ? filesize($fullPath) : 0
        ];
    };
    
    $results = [
        'gallery' => array_map(function($item) use ($checkFile) {
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'file_check' => $checkFile($item['image_url'])
            ];
        }, $gallery),
        'events' => array_map(function($item) use ($checkFile) {
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'file_check' => $checkFile($item['poster_url'])
            ];
        }, $events),
        'committee' => array_map(function($item) use ($checkFile) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'file_check' => $checkFile($item['image_url'])
            ];
        }, $committee),
        'sponsors' => array_map(function($item) use ($checkFile) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'file_check' => $checkFile($item['logo_url'])
            ];
        }, $sponsors),
        'tournaments' => array_map(function($item) use ($checkFile) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'file_check' => $checkFile($item['poster_url'])
            ];
        }, $tournaments)
    ];
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
