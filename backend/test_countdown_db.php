<?php
/**
 * Test countdown database operations
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
    
    echo "Testing countdown database operations...\n\n";
    
    // Test 1: Check if table exists and has correct structure
    echo "1. Checking table structure:\n";
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'event_countdown_settings' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "   - {$column['column_name']}: {$column['data_type']}\n";
    }
    echo "\n";
    
    // Test 2: Test UPDATE operation
    echo "2. Testing UPDATE operation:\n";
    try {
        $result = $database->execute("UPDATE event_countdown_settings SET is_active = false");
        echo "   ✅ UPDATE operation successful\n";
    } catch (Exception $e) {
        echo "   ❌ UPDATE operation failed: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Test INSERT operation
    echo "3. Testing INSERT operation:\n";
    try {
        $sql = "INSERT INTO event_countdown_settings (status_text, custom_message, target_date, is_active, show_countdown, countdown_type) VALUES (?, ?, ?, ?, ?, ?)";
        $result = $database->execute($sql, [
            'Test Countdown',
            'This is a test message',
            '2025-12-31 23:59:59',
            1,
            true,
            'days'
        ]);
        echo "   ✅ INSERT operation successful, ID: $result\n";
    } catch (Exception $e) {
        echo "   ❌ INSERT operation failed: " . $e->getMessage() . "\n";
    }
    
    // Test 4: Test SELECT operation
    echo "4. Testing SELECT operation:\n";
    try {
        $settings = $database->querySingle("SELECT * FROM event_countdown_settings WHERE is_active = true ORDER BY id DESC LIMIT 1");
        if ($settings) {
            echo "   ✅ SELECT operation successful\n";
            echo "   Data: " . json_encode($settings) . "\n";
        } else {
            echo "   ⚠️ SELECT operation successful but no active settings found\n";
        }
    } catch (Exception $e) {
        echo "   ❌ SELECT operation failed: " . $e->getMessage() . "\n";
    }
    
    // Test 5: Check current data
    echo "5. Current countdown settings:\n";
    try {
        $allSettings = $database->queryAll("SELECT * FROM event_countdown_settings ORDER BY id DESC");
        echo "   Total records: " . count($allSettings) . "\n";
        foreach ($allSettings as $setting) {
            echo "   - ID: {$setting['id']}, Status: {$setting['status_text']}, Active: " . ($setting['is_active'] ? 'Yes' : 'No') . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Failed to fetch current settings: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
