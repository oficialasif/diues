<?php
/**
 * Complete Database Setup Script for DIU Esports Community Portal
 * This script creates ALL necessary tables and default data
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
$messages = [];

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $messages[] = "✅ Database connection established";
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
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
    $messages[] = "✅ Users table created/verified";
    
    // Create committee_members table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS committee_members (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            role VARCHAR(100) NOT NULL,
            position VARCHAR(100) NOT NULL,
            image_url VARCHAR(255),
            bio TEXT,
            achievements TEXT,
            social_links JSONB,
            is_current BOOLEAN DEFAULT TRUE,
            year INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Committee members table created/verified";
    
    // Create games table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS games (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            genre VARCHAR(50) NOT NULL,
            description TEXT,
            image_url VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Games table created/verified";
    
    // Create tournaments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tournaments (
            id SERIAL PRIMARY KEY,
            game_id INTEGER NOT NULL,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            poster_url VARCHAR(255),
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            prize_pool DECIMAL(10,2),
            max_participants INTEGER,
            current_participants INTEGER DEFAULT 0,
            status VARCHAR(20) DEFAULT 'upcoming' CHECK (status IN ('upcoming', 'ongoing', 'completed', 'cancelled')),
            results JSONB,
            highlights_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        )
    ");
    $messages[] = "✅ Tournaments table created/verified";
    
    // Create tournament_registrations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tournament_registrations (
            id SERIAL PRIMARY KEY,
            tournament_id INTEGER NOT NULL,
            team_name VARCHAR(100) NOT NULL,
            team_type VARCHAR(10) NOT NULL CHECK (team_type IN ('solo', 'duo', 'squad')),
            captain_name VARCHAR(100) NOT NULL,
            captain_email VARCHAR(100) NOT NULL,
            captain_phone VARCHAR(20),
            captain_discord VARCHAR(100),
            captain_student_id VARCHAR(50),
            captain_department VARCHAR(100),
            captain_semester VARCHAR(20),
            status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
        )
    ");
    $messages[] = "✅ Tournament registrations table created/verified";
    
    // Create tournament_team_members table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tournament_team_members (
            id SERIAL PRIMARY KEY,
            registration_id INTEGER NOT NULL,
            player_name VARCHAR(100) NOT NULL,
            player_email VARCHAR(100),
            player_phone VARCHAR(20),
            player_discord VARCHAR(100),
            player_student_id VARCHAR(50),
            player_department VARCHAR(100),
            player_semester VARCHAR(20),
            player_role VARCHAR(20) DEFAULT 'member' CHECK (player_role IN ('captain', 'member', 'substitute')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (registration_id) REFERENCES tournament_registrations(id) ON DELETE CASCADE
        )
    ");
    $messages[] = "✅ Tournament team members table created/verified";
    
    // Create events table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id SERIAL PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            poster_url VARCHAR(255),
            event_date TIMESTAMP NOT NULL,
            location VARCHAR(200),
            event_type VARCHAR(20) NOT NULL CHECK (event_type IN ('tournament', 'meetup', 'workshop', 'celebration')),
            is_featured BOOLEAN DEFAULT FALSE,
            status VARCHAR(20) DEFAULT 'upcoming' CHECK (status IN ('upcoming', 'ongoing', 'completed', 'cancelled')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Events table created/verified";
    
    // Create gallery table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gallery (
            id SERIAL PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            image_url VARCHAR(255),
            video_url VARCHAR(255),
            category VARCHAR(20) NOT NULL CHECK (category IN ('tournament', 'event', 'achievement', 'community')),
            year INTEGER NOT NULL,
            tags JSONB,
            is_featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Gallery table created/verified";
    
    // Create achievements table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS achievements (
            id SERIAL PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            category VARCHAR(20) NOT NULL CHECK (category IN ('tournament', 'individual', 'team', 'community')),
            year INTEGER NOT NULL,
            icon_url VARCHAR(255),
            highlights_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Achievements table created/verified";
    
    // Create hall_of_fame table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS hall_of_fame (
            id SERIAL PRIMARY KEY,
            member_name VARCHAR(100) NOT NULL,
            achievement VARCHAR(200) NOT NULL,
            game VARCHAR(100),
            year INTEGER NOT NULL,
            image_url VARCHAR(255),
            stats JSONB,
            highlights_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Hall of fame table created/verified";
    
    // Create sponsors table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sponsors (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            logo_url VARCHAR(255),
            category VARCHAR(100),
            partnership_type VARCHAR(20) NOT NULL CHECK (partnership_type IN ('platinum', 'gold', 'silver', 'bronze')),
            website_url VARCHAR(255),
            benefits TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Sponsors table created/verified";
    
    // Create about_content table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS about_content (
            id SERIAL PRIMARY KEY,
            section_name VARCHAR(100) NOT NULL,
            title VARCHAR(200),
            content TEXT NOT NULL,
            image_url VARCHAR(255),
            order_index INTEGER DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ About content table created/verified";
    
    // Create site_settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            id SERIAL PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Site settings table created/verified";
    
    // Create event_countdown_settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS event_countdown_settings (
            id SERIAL PRIMARY KEY,
            event_title VARCHAR(200) NOT NULL,
            event_date TIMESTAMP NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = "✅ Event countdown settings table created/verified";
    
    // Check if admin user exists, if not create it
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin'");
    $stmt->execute(['admin']);
    $existingAdmin = $stmt->fetch();
    
    if (!$existingAdmin) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active) 
            VALUES (?, ?, ?, 'admin', TRUE)
        ");
        $stmt->execute(['admin', 'admin@diuesports.com', $hashedPassword]);
        $messages[] = "✅ Admin user created (username: admin, password: admin123)";
    } else {
        $messages[] = "✅ Admin user already exists";
    }
    
    // Insert default games if they don't exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM games");
    $gameCount = $stmt->fetchColumn();
    
    if ($gameCount == 0) {
        $defaultGames = [
            ['Valorant', 'FPS', 'Tactical shooter game'],
            ['PUBG Mobile', 'Battle Royale', 'Mobile battle royale game'],
            ['Free Fire', 'Battle Royale', 'Mobile battle royale game'],
            ['Call of Duty Mobile', 'FPS', 'Mobile first-person shooter'],
            ['Mobile Legends', 'MOBA', 'Mobile multiplayer online battle arena'],
            ['FIFA Mobile', 'Sports', 'Mobile football simulation game'],
            ['Clash Royale', 'Strategy', 'Real-time strategy game']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO games (name, genre, description, is_active) VALUES (?, ?, ?, TRUE)");
        foreach ($defaultGames as $game) {
            $stmt->execute($game);
        }
        $messages[] = "✅ Default games inserted";
    } else {
        $messages[] = "✅ Games already exist";
    }
    
    // Insert default about content if it doesn't exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM about_content");
    $aboutCount = $stmt->fetchColumn();
    
    if ($aboutCount == 0) {
        $defaultAbout = [
            ['mission', 'Our Mission', 'To foster a competitive gaming environment that promotes teamwork, strategic thinking, and sportsmanship while building a strong esports community at DIU.', 1],
            ['vision', 'Our Vision', 'To become the leading university esports community in Bangladesh, recognized for excellence in competitive gaming and community building.', 2],
            ['values', 'Core Values', 'Excellence, Teamwork, Innovation, Integrity, and Community Spirit', 3]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO about_content (section_name, title, content, order_index) VALUES (?, ?, ?, ?)");
        foreach ($defaultAbout as $about) {
            $stmt->execute($about);
        }
        $messages[] = "✅ Default about content inserted";
    } else {
        $messages[] = "✅ About content already exists";
    }
    
    // Insert default site settings if they don't exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_settings");
    $settingsCount = $stmt->fetchColumn();
    
    if ($settingsCount == 0) {
        $defaultSettings = [
            ['site_title', 'DIU ESPORTS COMMUNITY', 'Main website title'],
            ['site_description', 'Professional esports community at Daffodil International University', 'Website description'],
            ['contact_email', 'esports@diu.edu.bd', 'Primary contact email'],
            ['contact_phone', '+880 1234-567890', 'Primary contact phone'],
            ['address', 'Daffodil International University, Dhaka, Bangladesh', 'Physical address'],
            ['social_discord', 'https://discord.gg/diuesports', 'Discord server link'],
            ['social_twitch', 'https://twitch.tv/diuesports', 'Twitch channel'],
            ['social_facebook', 'https://facebook.com/diuesports', 'Facebook page'],
            ['social_youtube', 'https://youtube.com/diuesports', 'YouTube channel']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        $messages[] = "✅ Default site settings inserted";
    } else {
        $messages[] = "✅ Site settings already exist";
    }
    
    $success = "Database setup completed successfully! All tables created and default data inserted.";
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>DIU Esports - Complete Database Setup</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 40px; 
            background: #1a1a1a;
            color: #fff;
        }
        .container {
            max-width: 800px;
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
        .messages {
            background: #333;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .messages p {
            margin: 5px 0;
            font-family: monospace;
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
        <h1>🚀 DIU Esports Community - Complete Database Setup</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <h3>✅ <?php echo $success; ?></h3>
            </div>
            
            <div class="messages">
                <h4>Setup Details:</h4>
                <?php foreach ($messages as $message): ?>
                    <p><?php echo $message; ?></p>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="admin/login.php" class="btn">Go to Admin Login</a>
                <a href="api" class="btn">View API</a>
                <a href="test_db_connection.php" class="btn">Test Database</a>
            </div>
            
        <?php elseif ($error): ?>
            <div class="error">
                <h3>❌ <?php echo $error; ?></h3>
            </div>
            
            <?php if (!empty($messages)): ?>
                <div class="messages">
                    <h4>Partial Setup Details:</h4>
                    <?php foreach ($messages as $message): ?>
                        <p><?php echo $message; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
