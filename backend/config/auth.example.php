<?php
/**
 * Authentication Configuration Template for DIU Esports Community
 * 
 * IMPORTANT: Copy this file to auth.php and update with your actual authentication settings
 * DO NOT commit the actual auth.php file with real credentials to GitHub
 */

class Auth {
    private $db;
    private $session_timeout = 3600; // 1 hour in seconds

    public function __construct($database) {
        $this->db = $database;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login user with email and password
     */
    public function login($email, $password) {
        try {
            $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
            return false;
        }

        // Check session timeout
        if (time() - $_SESSION['login_time'] > $this->session_timeout) {
            $this->logout();
            return false;
        }

        return true;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'admin';
    }

    /**
     * Require admin access
     */
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: login.php');
            exit();
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
        session_start();
    }

    /**
     * Get current user info
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $sql = "SELECT id, email, role, created_at FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Generate CSRF token
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
