<?php
/**
 * Quick API Test - Check if data is being returned correctly
 */

header('Content-Type: application/json');

// Determine if we're in production or development
$isProduction = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'render.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false ||
    (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
);

if ($isProduction) {
    require_once __DIR__ . '/config/config.production.php';
    require_once __DIR__ . '/config/database.production.php';
} else {
    require_once __DIR__ . '/config/database.php';
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $results = [];
    
    // Test Events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
    $eventCount = $stmt->fetchColumn();
    $results['events'] = [
        'table_exists' => true,
        'count' => $eventCount,
        'sample_data' => []
    ];
    
    if ($eventCount > 0) {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 3");
        $results['events']['sample_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Test Committee
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM committee_members");
    $committeeCount = $stmt->fetchColumn();
    $results['committee'] = [
        'table_exists' => true,
        'count' => $committeeCount,
        'sample_data' => []
    ];
    
    if ($committeeCount > 0) {
        $stmt = $pdo->query("SELECT * FROM committee_members ORDER BY created_at DESC LIMIT 3");
        $results['committee']['sample_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Test Tournaments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tournaments");
    $tournamentCount = $stmt->fetchColumn();
    $results['tournaments'] = [
        'table_exists' => true,
        'count' => $tournamentCount,
        'sample_data' => []
    ];
    
    if ($tournamentCount > 0) {
        $stmt = $pdo->query("SELECT * FROM tournaments ORDER BY created_at DESC LIMIT 3");
        $results['tournaments']['sample_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Test Gallery
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
    $galleryCount = $stmt->fetchColumn();
    $results['gallery'] = [
        'table_exists' => true,
        'count' => $galleryCount,
        'sample_data' => []
    ];
    
    if ($galleryCount > 0) {
        $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 3");
        $results['gallery']['sample_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Test Sponsors
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sponsors");
    $sponsorCount = $stmt->fetchColumn();
    $results['sponsors'] = [
        'table_exists' => true,
        'count' => $sponsorCount,
        'sample_data' => []
    ];
    
    if ($sponsorCount > 0) {
        $stmt = $pdo->query("SELECT * FROM sponsors ORDER BY created_at DESC LIMIT 3");
        $results['sponsors']['sample_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database and API test completed',
        'data' => $results,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database test failed: ' . $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>
