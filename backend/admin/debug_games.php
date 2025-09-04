<?php
// Load common authentication
require_once 'auth_common.php';

// Require admin authentication
$auth->requireAdmin();

echo "<h1>Games Debug - Admin Context</h1>";

try {
    echo "<h2>Database Connection Test:</h2>";
    $pdo = $database->getConnection();
    echo "✅ Database connection successful<br>";
    
    echo "<h2>Games Table Check:</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) FROM games");
    $count = $stmt->fetchColumn();
    echo "Games table has $count records<br>";
    
    echo "<h2>Games Query Test:</h2>";
    $stmt = $pdo->query("SELECT id, name FROM games WHERE is_active = 1 ORDER BY name");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query returned " . count($games) . " games:<br>";
    echo "<ul>";
    foreach ($games as $game) {
        echo "<li>ID: {$game['id']}, Name: {$game['name']}</li>";
    }
    echo "</ul>";
    
    echo "<h2>Raw Games Data:</h2>";
    echo "<pre>" . print_r($games, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
