<?php
/**
 * PostgreSQL Database Setup Script for DIU Esports Community Portal
 * Run this script after deploying to Render to initialize the database
 */

// Load production configuration
require_once __DIR__ . '/config/config.production.php';
require_once __DIR__ . '/config/database.production.php';

echo "🚀 DIU Esports Community Portal - PostgreSQL Database Setup\n";
echo "========================================================\n\n";

try {
    // Create database connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "✅ Database connection established successfully\n";
    
    // Read and execute PostgreSQL schema
    $schemaFile = __DIR__ . '/config/schema.postgresql.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Split schema into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) { return !empty($stmt) && !preg_match('/^--/', $stmt); }
    );
    
    echo "📋 Executing PostgreSQL schema...\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $successCount++;
            echo "  ✅ Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            $errorCount++;
            echo "  ❌ Error: " . $e->getMessage() . "\n";
            echo "  Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    echo "\n📊 Schema Execution Summary:\n";
    echo "  ✅ Successful statements: $successCount\n";
    echo "  ❌ Failed statements: $errorCount\n";
    
    // Verify tables were created
    echo "\n🔍 Verifying database structure...\n";
    
    $requiredTables = [
        'users', 'committee_members', 'games', 'tournaments', 
        'tournament_registrations', 'tournament_team_members', 'events',
        'gallery', 'achievements', 'hall_of_fame', 'sponsors',
        'about_content', 'site_settings', 'event_countdown_settings'
    ];
    
    $existingTables = [];
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    while ($row = $stmt->fetch()) {
        $existingTables[] = $row['table_name'];
    }
    
    $missingTables = array_diff($requiredTables, $existingTables);
    
    if (empty($missingTables)) {
        echo "✅ All required tables created successfully\n";
    } else {
        echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
    }
    
    // Verify admin user
    echo "\n🔐 Verifying admin user...\n";
    $stmt = $pdo->prepare("SELECT username, email, role FROM users WHERE username = 'asifmahmud'");
    $stmt->execute();
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "✅ Admin user created: {$adminUser['username']} ({$adminUser['email']})\n";
        echo "   Role: {$adminUser['role']}\n";
    } else {
        echo "❌ Admin user not found\n";
    }
    
    // Verify default games
    echo "\n🎮 Verifying default games...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM games");
    $gameCount = $stmt->fetch()['count'];
    echo "✅ Default games created: $gameCount games\n";
    
    // Verify site settings
    echo "\n⚙️ Verifying site settings...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM site_settings");
    $settingCount = $stmt->fetch()['count'];
    echo "✅ Site settings created: $settingCount settings\n";
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "========================================\n";
    echo "Your DIU Esports Community Portal is ready to use!\n";
    echo "Admin login: asifmahmud / admin*diuEsports\n";
    echo "Database: PostgreSQL on Render\n";
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
    exit(1);
}
?>
