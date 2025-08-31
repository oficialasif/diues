<?php
/**
 * Fix Admin Login Issues
 * This script fixes the database schema and creates the admin user
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== FIXING ADMIN LOGIN ISSUES ===\n\n";
    
    // 1. Check if users table exists and has correct structure
    echo "1. Checking users table structure...\n";
    
    try {
        // Check if is_active column exists
        $stmt = $conn->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('is_active', $columns)) {
            echo "   Adding missing 'is_active' column...\n";
            $conn->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
            echo "   ✅ 'is_active' column added\n";
        } else {
            echo "   ✅ 'is_active' column exists\n";
        }
        
        // Check if role column exists
        if (!in_array('role', $columns)) {
            echo "   Adding missing 'role' column...\n";
            $conn->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'moderator') DEFAULT 'admin'");
            echo "   ✅ 'role' column added\n";
        } else {
            echo "   ✅ 'role' column exists\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error checking table structure: " . $e->getMessage() . "\n";
        
        // If table doesn't exist, create it
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            echo "   Creating users table...\n";
            $sql = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin', 'moderator') DEFAULT 'admin',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->exec($sql);
            echo "   ✅ Users table created\n";
        }
    }
    
    // 2. Check if admin user exists
    echo "\n2. Checking admin user...\n";
    
    try {
        $stmt = $conn->prepare("SELECT id, username, role, is_active FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "   ✅ Admin user exists (ID: {$admin['id']}, Role: {$admin['role']}, Active: " . ($admin['is_active'] ? 'Yes' : 'No') . ")\n";
            
            // Update admin user to ensure it's active and has admin role
            $update_sql = "UPDATE users SET role = 'admin', is_active = TRUE WHERE username = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->execute(['admin']);
            echo "   ✅ Admin user updated\n";
            
        } else {
            echo "   ❌ Admin user not found. Creating...\n";
            
            // Create admin user with password 'admin123'
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, 'admin', TRUE)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->execute(['admin', 'admin@diuesports.com', $password_hash]);
            
            echo "   ✅ Admin user created with username: admin, password: admin123\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error with admin user: " . $e->getMessage() . "\n";
    }
    
    // 3. Test authentication
    echo "\n3. Testing authentication...\n";
    
    try {
        require_once 'config/auth.php';
        $auth = new Auth($database);
        
        $result = $auth->login('admin', 'admin123');
        
        if ($result['success']) {
            echo "   ✅ Authentication test successful\n";
            echo "   User: " . $result['user']['username'] . " (Role: " . $result['user']['role'] . ")\n";
        } else {
            echo "   ❌ Authentication test failed: " . $result['message'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error testing authentication: " . $e->getMessage() . "\n";
    }
    
    // 4. Check other essential tables
    echo "\n4. Checking essential tables...\n";
    
    $essential_tables = ['games', 'tournaments', 'events', 'committee_members', 'gallery', 'sponsors'];
    
    foreach ($essential_tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   ✅ Table '$table': {$result['count']} records\n";
        } catch (Exception $e) {
            echo "   ❌ Table '$table': " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== FIX COMPLETED ===\n";
    echo "✅ Admin login should now work\n";
    echo "✅ Username: admin\n";
    echo "✅ Password: admin123\n";
    echo "✅ Navigate to: /backend/admin/login.php\n";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
}
?>
