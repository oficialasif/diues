<?php
/**
 * Update event_countdown_settings table structure
 * This script updates the table to match the admin form requirements
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
    
    echo "Updating event_countdown_settings table structure...\n";
    
    // Check if the new columns exist
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'event_countdown_settings'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $needsUpdate = false;
    
    // Add missing columns
    if (!in_array('status_text', $columns)) {
        $pdo->exec("ALTER TABLE event_countdown_settings ADD COLUMN status_text VARCHAR(200)");
        $needsUpdate = true;
        echo "Added status_text column\n";
    }
    
    if (!in_array('custom_message', $columns)) {
        $pdo->exec("ALTER TABLE event_countdown_settings ADD COLUMN custom_message TEXT");
        $needsUpdate = true;
        echo "Added custom_message column\n";
    }
    
    if (!in_array('target_date', $columns)) {
        $pdo->exec("ALTER TABLE event_countdown_settings ADD COLUMN target_date TIMESTAMP");
        $needsUpdate = true;
        echo "Added target_date column\n";
    }
    
    if (!in_array('show_countdown', $columns)) {
        $pdo->exec("ALTER TABLE event_countdown_settings ADD COLUMN show_countdown BOOLEAN DEFAULT TRUE");
        $needsUpdate = true;
        echo "Added show_countdown column\n";
    }
    
    if (!in_array('countdown_type', $columns)) {
        $pdo->exec("ALTER TABLE event_countdown_settings ADD COLUMN countdown_type VARCHAR(20) DEFAULT 'days'");
        $needsUpdate = true;
        echo "Added countdown_type column\n";
    }
    
    // Migrate data from old columns to new columns if they exist
    if (in_array('event_title', $columns) && in_array('status_text', $columns)) {
        $pdo->exec("UPDATE event_countdown_settings SET status_text = event_title WHERE status_text IS NULL OR status_text = ''");
        echo "Migrated event_title to status_text\n";
    }
    
    if (in_array('event_date', $columns) && in_array('target_date', $columns)) {
        $pdo->exec("UPDATE event_countdown_settings SET target_date = event_date WHERE target_date IS NULL");
        echo "Migrated event_date to target_date\n";
    }
    
    if ($needsUpdate) {
        echo "Table structure updated successfully!\n";
    } else {
        echo "Table structure is already up to date.\n";
    }
    
    // Test the table
    $stmt = $pdo->query("SELECT * FROM event_countdown_settings LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "Table test successful. Current structure:\n";
        print_r(array_keys($result));
    } else {
        echo "Table is empty but structure is correct.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
