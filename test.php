<?php
/**
 * DIU Esports Community Backend Test File
 * Use this to test if the backend is working correctly
 */

echo "<h1>DIU Esports Community Backend</h1>";
echo "<p>Backend is working correctly!</p>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Test a simple query
        try {
            $result = $database->querySingle("SELECT 1 as test");
            if ($result) {
                echo "<p style='color: green;'>✓ Database query test successful</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Database query test failed: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database configuration error: " . $e->getMessage() . "</p>";
}

// Test authentication system
echo "<h2>Authentication System Test</h2>";
try {
    require_once 'config/auth.php';
    $auth = new Auth($database);
    echo "<p style='color: green;'>✓ Authentication system loaded successfully</p>";
    
    // Test CSRF token generation
    $token = $auth->generateCSRFToken();
    if ($token) {
        echo "<p style='color: green;'>✓ CSRF token generation working</p>";
        
        // Test token verification
        if ($auth->verifyCSRFToken($token)) {
            echo "<p style='color: green;'>✓ CSRF token verification working</p>";
        } else {
            echo "<p style='color: red;'>✗ CSRF token verification failed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ CSRF token generation failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Authentication system error: " . $e->getMessage() . "</p>";
}

// Test file permissions
echo "<h2>File Permissions Test</h2>";
$upload_dirs = ['uploads/posters', 'uploads/photos', 'uploads/logos', 'uploads/icons', 'uploads/highlights'];
foreach ($upload_dirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "<p style='color: green;'>✓ Directory '$dir' exists and is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Directory '$dir' missing or not writable</p>";
    }
}

// Test PHP extensions
echo "<h2>PHP Extensions Test</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ Extension '$ext' loaded</p>";
    } else {
        echo "<p style='color: red;'>✗ Extension '$ext' not loaded</p>";
    }
}

// Test PHP version
echo "<h2>PHP Version</h2>";
echo "<p>Current PHP version: " . phpversion() . "</p>";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "<p style='color: green;'>✓ PHP version is compatible (7.4+ required)</p>";
} else {
    echo "<p style='color: red;'>✗ PHP version is too old. 7.4+ required</p>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> Delete this file after testing for security reasons.</p>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>
