<?php
/**
 * Complete Admin Setup Script
 * This script sets up the entire admin system
 */

echo "=== DIU ESPORTS ADMIN SETUP ===\n\n";

// Step 1: Check and create database
echo "Step 1: Database Setup\n";
echo "======================\n";

try {
    // First, try to connect without specifying database
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS diu_esports CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'diu_esports' ready\n";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=localhost;dbname=diu_esports", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Step 2: Create tables
echo "\nStep 2: Table Creation\n";
echo "======================\n";

$tables = [
    'users' => "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'moderator') DEFAULT 'admin',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'games' => "CREATE TABLE games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        genre VARCHAR(50) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'tournaments' => "CREATE TABLE tournaments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        game_id INT NOT NULL,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        poster_url VARCHAR(255),
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        prize_pool DECIMAL(10,2),
        max_participants INT,
        current_participants INT DEFAULT 0,
        status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
        results JSON,
        highlights_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'events' => "CREATE TABLE events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        poster_url VARCHAR(255),
        event_date DATETIME NOT NULL,
        location VARCHAR(200),
        event_type ENUM('tournament', 'meetup', 'workshop', 'celebration') NOT NULL,
        is_featured BOOLEAN DEFAULT FALSE,
        status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'committee_members' => "CREATE TABLE committee_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        role VARCHAR(100) NOT NULL,
        position VARCHAR(100) NOT NULL,
        image_url VARCHAR(255),
        bio TEXT,
        achievements TEXT,
        social_links JSON,
        is_current BOOLEAN DEFAULT TRUE,
        year INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'gallery' => "CREATE TABLE gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        video_url VARCHAR(255),
        category ENUM('tournament', 'event', 'achievement', 'community') NOT NULL,
        year INT NOT NULL,
        tags JSON,
        is_featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'sponsors' => "CREATE TABLE sponsors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        logo_url VARCHAR(255),
        category VARCHAR(100),
        partnership_type ENUM('platinum', 'gold', 'silver', 'bronze') NOT NULL,
        website_url VARCHAR(255),
        benefits TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $table_name => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Table '$table_name' created\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "already exists") !== false) {
            echo "✅ Table '$table_name' already exists\n";
        } else {
            echo "❌ Error creating table '$table_name': " . $e->getMessage() . "\n";
        }
    }
}

// Step 3: Insert sample data
echo "\nStep 3: Sample Data\n";
echo "===================\n";

// Insert default games
$games = [
    ['Valorant', 'FPS', 'Tactical shooter game'],
    ['PUBG Mobile', 'Battle Royale', 'Mobile battle royale game'],
    ['Free Fire', 'Battle Royale', 'Mobile battle royale game'],
    ['Call of Duty Mobile', 'FPS', 'Mobile first-person shooter'],
    ['Mobile Legends', 'MOBA', 'Mobile multiplayer online battle arena'],
    ['FIFA Mobile', 'Sports', 'Mobile football simulation game'],
    ['Clash Royale', 'Strategy', 'Real-time strategy game']
];

foreach ($games as $game) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO games (name, genre, description) VALUES (?, ?, ?)");
        $stmt->execute($game);
        echo "✅ Game '{$game[0]}' added\n";
    } catch (Exception $e) {
        echo "ℹ️  Game '{$game[0]}' already exists\n";
    }
}

// Step 4: Create admin user
echo "\nStep 4: Admin User Setup\n";
echo "========================\n";

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        // Update existing admin user
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, role = 'admin', is_active = TRUE WHERE username = ?");
        $stmt->execute([$password_hash, 'admin']);
        echo "✅ Admin user updated\n";
    } else {
        // Create new admin user
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, 'admin', TRUE)");
        $stmt->execute(['admin', 'admin@diuesports.com', $password_hash]);
        echo "✅ Admin user created\n";
    }
    
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
    echo "   Role: admin\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up admin user: " . $e->getMessage() . "\n";
}

// Step 5: Test the system
echo "\nStep 5: System Test\n";
echo "===================\n";

try {
    require_once 'config/database.php';
    require_once 'config/auth.php';
    
    $database = new Database();
    $auth = new Auth($database);
    
    $result = $auth->login('admin', 'admin123');
    
    if ($result['success']) {
        echo "✅ Authentication test successful\n";
        echo "✅ Admin login working correctly\n";
    } else {
        echo "❌ Authentication test failed: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ System test failed: " . $e->getMessage() . "\n";
}

echo "\n=== SETUP COMPLETED ===\n";
echo "🎉 Admin system is ready!\n";
echo "📱 Access admin panel at: /backend/admin/login.php\n";
echo "🔑 Login with: admin / admin123\n";
echo "⚠️  Remember to change the default password after first login!\n";
?>
