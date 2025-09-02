<?php
/**
 * Database Setup Script for DIU Esports Community Portal
 * Run this to set up the database tables and default admin user
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
    
    // Check if users table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users')");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // Create users table
        $pdo->exec("
            CREATE TABLE users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'admin' CHECK (role IN ('admin', 'moderator')),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active) 
            VALUES (?, ?, ?, 'admin', TRUE)
        ");
        $stmt->execute(['admin', 'admin@diuesports.com', $hashedPassword]);
        
        $success = "Database setup completed! Admin user created with username: 'admin' and password: 'admin123'";
    } else {
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute(['admin']);
        $existingAdmin = $stmt->fetch();
        
        if ($existingAdmin) {
            $success = "Database already set up! Admin user exists with username: 'admin' and password: 'admin123'";
        } else {
            // Create admin user
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role, is_active) 
                VALUES (?, ?, ?, 'admin', TRUE)
            ");
            $stmt->execute(['admin', 'admin@diuesports.com', $hashedPassword]);
            
            $success = "Admin user created! Username: 'admin', Password: 'admin123'";
        }
    }
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>DIU Esports - Database Setup</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 40px; 
            background: #1a1a1a;
            color: #fff;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
            border: 2px solid #22C55E;
        }
        .success { 
            color: #22C55E; 
            background: #22C55E20;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error { 
            color: #ef4444; 
            background: #ef444420;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            background: #22C55E;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #16a34a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 DIU Esports Community - Database Setup</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <h3>✅ <?php echo $success; ?></h3>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="admin/login.php" class="btn">Go to Admin Login</a>
                <a href="api" class="btn">View API</a>
            </div>
            
        <?php elseif ($error): ?>
            <div class="error">
                <h3>❌ <?php echo $error; ?></h3>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
