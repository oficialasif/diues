<?php
/**
 * Common Authentication for Admin Panel
 * This file provides consistent authentication across all admin pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine if we're in production or development
$isProduction = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'render.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false ||
    (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production')
);

// Load appropriate database configuration
if ($isProduction) {
    // Load production configuration for Render
    require_once __DIR__ . '/../config/config.production.php';
    require_once __DIR__ . '/../config/database.production.php';
} else {
    // Load local configuration for XAMPP
    require_once __DIR__ . '/../config/database.php';
}

// Try to load auth.php, if it fails, create a simple auth class
if (file_exists(__DIR__ . '/../config/auth.php')) {
    require_once __DIR__ . '/../config/auth.php';
    $database = new Database();
    $auth = new Auth($database);
} else {
    // Simple auth class for production
    class SimpleAuth {
        private $db;
        
        public function __construct($database) {
            $this->db = $database;
        }
        
        public function login($username, $password) {
            try {
                $sql = "SELECT id, username, email, password_hash, role FROM users WHERE username = ?";
                $user = $this->db->querySingle($sql, [$username]);
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    
                    return [
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'email' => $user['email'],
                            'role' => $user['role']
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Invalid username or password'
                    ];
                }
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Login failed: ' . $e->getMessage()
                ];
            }
        }
        
        public function isLoggedIn() {
            if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
                return false;
            }
            
            if (time() - $_SESSION['last_activity'] > 28800) {
                $this->logout();
                return false;
            }
            
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        public function logout() {
            session_unset();
            session_destroy();
            return ['success' => true, 'message' => 'Logged out successfully'];
        }
        
        public function requireAuth() {
            if (!$this->isLoggedIn()) {
                header('Location: login.php');
                exit();
            }
        }
        
        public function requireAdmin() {
            if (!$this->isAdmin()) {
                header('Location: login.php?error=access_denied');
                exit();
            }
        }
        
        public function isAdmin() {
            return $this->isLoggedIn() && $_SESSION['role'] === 'admin';
        }
        
        public function isModerator() {
            return $this->isLoggedIn() && ($_SESSION['role'] === 'moderator' || $_SESSION['role'] === 'admin');
        }
        
        public function getCurrentUser() {
            if (!$this->isLoggedIn()) {
                return null;
            }
            
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ];
        }
        
        public function generateCSRFToken() {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }
        
        public function verifyCSRFToken($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
        
        public function sanitizeInput($data) {
            if (is_array($data)) {
                return array_map([$this, 'sanitizeInput'], $data);
            }
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }
    
    $database = new Database();
    $auth = new SimpleAuth($database);
}
?>
