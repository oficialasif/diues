<?php
// Simple test without authentication to debug games loading
echo "<h1>Games Test (No Auth)</h1>";

try {
    // Load database configuration
    $isProduction = isset($_SERVER['HTTP_HOST']) && (
        strpos($_SERVER['HTTP_HOST'], 'render.com') !== false ||
        strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false ||
        (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
    );
    
    if ($isProduction) {
        require_once __DIR__ . '/../config/config.production.php';
        require_once __DIR__ . '/../config/database.production.php';
    } else {
        require_once __DIR__ . '/../config/database.php';
    }
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h2>Database Connection:</h2>";
    echo "✅ Connected successfully<br>";
    
    echo "<h2>Games Table Check:</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) FROM games");
    $count = $stmt->fetchColumn();
    echo "Total games in table: $count<br>";
    
    echo "<h2>Active Games Query:</h2>";
    $stmt = $pdo->query("SELECT id, name FROM games WHERE is_active = true ORDER BY name");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Active games found: " . count($games) . "<br>";
    echo "<ul>";
    foreach ($games as $game) {
        echo "<li>ID: {$game['id']}, Name: {$game['name']}</li>";
    }
    echo "</ul>";
    
    echo "<h2>Database Class Test:</h2>";
    $gamesFromClass = $database->queryAll("SELECT id, name FROM games WHERE is_active = true ORDER BY name");
    echo "Games from database class: " . count($gamesFromClass) . "<br>";
    echo "<pre>" . print_r($gamesFromClass, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
