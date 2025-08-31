<?php
/**
 * Check Admin User in Database
 */

echo "=== CHECKING ADMIN USER ===\n\n";

try {
    require_once 'config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "1. Database connection...\n";
    echo "   ✅ Connected to database\n";
    
    echo "\n2. Checking users table...\n";
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Users table exists\n";
    } else {
        echo "   ❌ Users table does not exist\n";
        exit(1);
    }
    
    echo "\n3. Checking users table structure...\n";
    $stmt = $conn->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Columns: " . implode(', ', $columns) . "\n";
    
    echo "\n4. Checking admin user...\n";
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "   ✅ Admin user found:\n";
        foreach ($admin as $key => $value) {
            if ($key === 'password_hash') {
                echo "      $key: " . substr($value, 0, 20) . "...\n";
            } else {
                echo "      $key: $value\n";
            }
        }
        
        echo "\n5. Testing password verification...\n";
        if (password_verify('admin123', $admin['password_hash'])) {
            echo "   ✅ Password 'admin123' is correct\n";
        } else {
            echo "   ❌ Password 'admin123' is incorrect\n";
            
            echo "\n6. Creating new password hash...\n";
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
            $update_stmt->execute([$new_hash, 'admin']);
            echo "   ✅ Password hash updated\n";
        }
        
    } else {
        echo "   ❌ Admin user not found\n";
        
        echo "\n6. Creating admin user...\n";
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, 'admin', TRUE)");
        $insert_stmt->execute(['admin', 'admin@diuesports.com', $password_hash]);
        echo "   ✅ Admin user created\n";
    }
    
    echo "\n=== CHECK COMPLETED ===\n";
    echo "🔑 Admin credentials: admin / admin123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
