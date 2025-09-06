<?php
/**
 * Migration Script: Add game_username column to tournament_team_members table
 * Run this script to fix the missing column issue
 */

header('Content-Type: text/html; charset=utf-8');

// Determine if we're in production or development
$isProduction = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'render.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false ||
    (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
);

if ($isProduction) {
    // Load production configuration for Render
    require_once __DIR__ . '/config/config.production.php';
    require_once __DIR__ . '/config/database.production.php';
} else {
    // Load local configuration for XAMPP
    require_once __DIR__ . '/config/database.php';
}

$error = '';
$success = '';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h2>DIU Esports - Database Migration: Add game_username Column</h2>";
    echo "<hr>";
    
    // Check if column already exists
    $checkColumn = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'tournament_team_members' 
        AND column_name = 'game_username'
    ");
    
    if ($checkColumn->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ Column 'game_username' already exists in tournament_team_members table.</p>";
    } else {
        // Add the column
        $sql = "ALTER TABLE tournament_team_members ADD COLUMN game_username VARCHAR(100)";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Successfully added 'game_username' column to tournament_team_members table.</p>";
        
        // Add comment
        $sql = "COMMENT ON COLUMN tournament_team_members.game_username IS 'Player in-game username for the tournament'";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Successfully added comment to game_username column.</p>";
    }
    
    // Verify the column exists
    $verifyColumn = $pdo->query("
        SELECT column_name, data_type, character_maximum_length 
        FROM information_schema.columns 
        WHERE table_name = 'tournament_team_members' 
        AND column_name = 'game_username'
    ");
    
    if ($verifyColumn->rowCount() > 0) {
        $columnInfo = $verifyColumn->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Verification successful: Column '{$columnInfo['column_name']}' exists with type {$columnInfo['data_type']}({$columnInfo['character_maximum_length']})</p>";
    } else {
        echo "<p style='color: red;'>❌ Verification failed: Column 'game_username' not found.</p>";
    }
    
    echo "<hr>";
    echo "<p style='color: blue;'>🎉 Migration completed! The tournament registration system should now work properly.</p>";
    echo "<p><a href='admin/tournaments.php'>Go to Tournaments Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>
