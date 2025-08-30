<?php
/**
 * Check database status and data
 * This script shows what's currently in the database
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== DATABASE STATUS CHECK ===\n\n";
    
    // Check if tables exist
    $tables = ['games', 'tournaments', 'events', 'committee_members', 'gallery', 'sponsors', 'achievements'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Table '$table': {$result['count']} records\n";
        } catch (Exception $e) {
            echo "Table '$table': ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== SAMPLE DATA ===\n\n";
    
    // Show sample games
    echo "Games:\n";
    try {
        $stmt = $conn->query("SELECT id, name, genre FROM games LIMIT 5");
        $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($games)) {
            echo "  No games found\n";
        } else {
            foreach ($games as $game) {
                echo "  ID: {$game['id']}, Name: {$game['name']}, Genre: {$game['genre']}\n";
            }
        }
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    
    // Show sample tournaments
    echo "\nTournaments:\n";
    try {
        $stmt = $conn->query("SELECT id, name, game_id, status FROM tournaments LIMIT 5");
        $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($tournaments)) {
            echo "  No tournaments found\n";
        } else {
            foreach ($tournaments as $tournament) {
                echo "  ID: {$tournament['id']}, Name: {$tournament['name']}, Game ID: {$tournament['game_id']}, Status: {$tournament['status']}\n";
            }
        }
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "1. If no games exist, run: php add_games.php\n";
    echo "2. If no tournaments exist, run: php add_sample_data.php\n";
    echo "3. Make sure the database 'diu_esports' exists and tables are created\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
