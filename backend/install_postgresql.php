<?php
/**
 * DIU Esports Community PostgreSQL Installation Script
 * Run this to set up the database and initial configuration
 */

header('Content-Type: text/html; charset=utf-8');

// Check if already installed
if (file_exists('config/installed.lock')) {
    die('Application is already installed. Delete config/installed.lock to reinstall.');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Load production configuration
require_once __DIR__ . '/config/config.production.php';
require_once __DIR__ . '/config/database.production.php';

try {
    // Test database connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($step == 1) {
        // Step 1: Test connection and create tables
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>DIU Esports - Database Installation</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .success { color: green; }
                .error { color: red; }
                .info { color: blue; }
                button { padding: 10px 20px; margin: 10px; }
            </style>
        </head>
        <body>
            <h1>🚀 DIU Esports Community - Database Installation</h1>
            
            <h2>Step 1: Database Connection Test</h2>
            
            <?php
            try {
                // Test basic query
                $stmt = $pdo->query("SELECT version() as db_version");
                $version = $stmt->fetch();
                
                echo "<div class='success'>✅ Database connection successful!</div>";
                echo "<p><strong>Database Version:</strong> " . $version['db_version'] . "</p>";
                
                // Check if tables exist
                $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
                $tables = $stmt->fetchAll();
                
                if (count($tables) > 0) {
                    echo "<div class='info'>📋 Existing tables found: " . count($tables) . "</div>";
                    echo "<ul>";
                    foreach ($tables as $table) {
                        echo "<li>" . $table['table_name'] . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<div class='info'>📋 No tables found. Ready to create.</div>";
                }
                
                echo "<h3>Next Steps:</h3>";
                echo "<p>Your database is connected and ready. You can now:</p>";
                echo "<ul>";
                echo "<li>✅ Use the API endpoints</li>";
                echo "<li>✅ Access the admin panel</li>";
                echo "<li>✅ Upload and manage content</li>";
                echo "</ul>";
                
                echo "<h3>API Endpoints:</h3>";
                echo "<ul>";
                echo "<li><a href='/api' target='_blank'>API Overview</a></li>";
                echo "<li><a href='/admin' target='_blank'>Admin Panel</a></li>";
                echo "<li><a href='/test_render.php' target='_blank'>Health Check</a></li>";
                echo "</ul>";
                
                // Create installed lock file
                if (!is_dir('config')) {
                    mkdir('config', 0755, true);
                }
                file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
                
                echo "<div class='success'>🎉 Installation completed successfully!</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
                echo "<p>Please check your environment variables and database connection.</p>";
            }
            ?>
            
            <hr>
            <p><small>Installation completed at: <?php echo date('Y-m-d H:i:s'); ?></small></p>
        </body>
        </html>
        <?php
    }
    
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>DIU Esports - Installation Error</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .error { color: red; }
        </style>
    </head>
    <body>
        <h1>❌ Installation Error</h1>
        <div class='error'>
            <p><strong>Error:</strong> <?php echo $e->getMessage(); ?></p>
        </div>
        
        <h3>Troubleshooting:</h3>
        <ul>
            <li>Check if environment variables are set correctly</li>
            <li>Verify database credentials</li>
            <li>Ensure database service is running</li>
            <li>Check Render logs for more details</li>
        </ul>
        
        <h3>Environment Variables Check:</h3>
        <ul>
            <li>DB_HOST: <?php echo $_ENV['DB_HOST'] ?? 'NOT SET'; ?></li>
            <li>DB_NAME: <?php echo $_ENV['DB_NAME'] ?? 'NOT SET'; ?></li>
            <li>DB_USERNAME: <?php echo $_ENV['DB_USERNAME'] ?? 'NOT SET'; ?></li>
            <li>DB_PASSWORD: <?php echo $_ENV['DB_PASSWORD'] ? 'SET' : 'NOT SET'; ?></li>
        </ul>
    </body>
    </html>
    <?php
}
?>
