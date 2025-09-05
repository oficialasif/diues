<?php
/**
 * Fix event_countdown_settings table constraints
 */

// Determine if we're in production or development
$isProduction = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'render.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false ||
    (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
);

try {
    if ($isProduction) {
        require_once __DIR__ . '/config/config.production.php';
        require_once __DIR__ . '/config/database.production.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "Fixing event_countdown_settings table constraints...\n\n";
    
    // Remove NOT NULL constraints from old columns
    try {
        $pdo->exec("ALTER TABLE event_countdown_settings ALTER COLUMN event_title DROP NOT NULL");
        echo "✅ Removed NOT NULL constraint from event_title\n";
    } catch (Exception $e) {
        echo "⚠️ Could not remove NOT NULL from event_title: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE event_countdown_settings ALTER COLUMN event_date DROP NOT NULL");
        echo "✅ Removed NOT NULL constraint from event_date\n";
    } catch (Exception $e) {
        echo "⚠️ Could not remove NOT NULL from event_date: " . $e->getMessage() . "\n";
    }
    
    // Test the table structure
    echo "\nTesting table structure:\n";
    $stmt = $pdo->query("SELECT column_name, is_nullable FROM information_schema.columns WHERE table_name = 'event_countdown_settings' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
        echo "   - {$column['column_name']}: {$nullable}\n";
    }
    
    echo "\nTable constraints fixed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
