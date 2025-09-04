<?php
/**
 * Run Database Setup Script
 * This will create all tables and insert default data
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>DIU Esports Database Setup</h1>";
echo "<p>Running database setup...</p>";

try {
    // Include the setup script
    require_once __DIR__ . '/setup_complete_database.php';
    
    echo "<h2>Setup Completed Successfully!</h2>";
    echo "<p>All tables have been created and default data has been inserted.</p>";
    
    // Test the games table specifically
    echo "<h3>Testing Games Table:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) FROM games");
    $gameCount = $stmt->fetchColumn();
    echo "<p>Games table has $gameCount records</p>";
    
    if ($gameCount > 0) {
        $stmt = $pdo->query("SELECT name, genre FROM games ORDER BY name");
        $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($games as $game) {
            echo "<li>{$game['name']} ({$game['genre']})</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<h2>Setup Failed!</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
