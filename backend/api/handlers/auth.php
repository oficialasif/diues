<?php
/**
 * Authentication API Handler
 * Manages all authentication-related operations
 */

class AuthHandler {
    private $db;
    private $auth;
    
    public function __construct($database, $auth) {
        $this->db = $database;
        $this->auth = $auth;
    }
    
    /**
     * Handle POST requests for authentication
     */
    public function postAction($action) {
        switch ($action) {
            case 'login':
                return $this->login();
            case 'logout':
                return $this->logout();
            case 'register':
                return $this->register();
            case 'change-password':
                return $this->changePassword();
            case 'reset-password':
                return $this->resetPassword();
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Handle GET requests for authentication
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'profile':
                return $this->getProfile();
            case 'check':
                return $this->checkAuth();
            case 'permissions':
                return $this->getPermissions();
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * User login
     */
    private function login() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['username', 'password'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            $username = $input['username'];
            $password = $input['password'];
            
            // Attempt login
            $result = $this->auth->login($username, $password);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'data' => $result['user'],
                    'message' => 'Login successful'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message']
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
     * User logout
     */
    private function logout() {
        try {
            $result = $this->auth->logout();
            
            return [
                'success' => true,
                'data' => null,
                'message' => $result['message']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * User registration (admin only)
     */
    private function register() {
        try {
            // Check if user is admin
            if (!$this->auth->isAdmin()) {
                return [
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.'
                ];
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['username', 'email', 'password', 'role'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Validate email format
            if (!$this->auth->validateEmail($input['email'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format'
                ];
            }
            
            // Validate role
            $valid_roles = ['admin', 'moderator'];
            if (!in_array($input['role'], $valid_roles)) {
                return [
                    'success' => false,
                    'message' => 'Invalid role. Must be one of: ' . implode(', ', $valid_roles)
                ];
            }
            
            // Check if username already exists
            $existing_user = $this->db->querySingle("SELECT id FROM users WHERE username = ?", [$input['username']]);
            if ($existing_user) {
                return [
                    'success' => false,
                    'message' => 'Username already exists'
                ];
            }
            
            // Check if email already exists
            $existing_email = $this->db->querySingle("SELECT id FROM users WHERE email = ?", [$input['email']]);
            if ($existing_email) {
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }
            
            // Hash password
            $password_hash = $this->auth->hashPassword($input['password']);
            
            // Create user
            $sql = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
            $user_id = $this->db->execute($sql, [
                $input['username'],
                $input['email'],
                $password_hash,
                $input['role']
            ]);
            
            if ($user_id) {
                return [
                    'success' => true,
                    'data' => [
                        'id' => $user_id,
                        'username' => $input['username'],
                        'email' => $input['email'],
                        'role' => $input['role']
                    ],
                    'message' => 'User registered successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to register user'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Change password
     */
    private function changePassword() {
        try {
            // Check if user is logged in
            if (!$this->auth->isLoggedIn()) {
                return [
                    'success' => false,
                    'message' => 'Authentication required'
                ];
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['current_password', 'new_password'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            $current_user = $this->auth->getCurrentUser();
            
            // Verify current password
            $user = $this->db->querySingle("SELECT password_hash FROM users WHERE id = ?", [$current_user['id']]);
            if (!password_verify($input['current_password'], $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Hash new password
            $new_password_hash = $this->auth->hashPassword($input['new_password']);
            
            // Update password
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $result = $this->db->execute($sql, [$new_password_hash, $current_user['id']]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => null,
                    'message' => 'Password changed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to change password'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Password change failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Reset password (admin only)
     */
    private function resetPassword() {
        try {
            // Check if user is admin
            if (!$this->auth->isAdmin()) {
                return [
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.'
                ];
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['user_id', 'new_password'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Check if user exists
            $existing_user = $this->db->querySingle("SELECT id FROM users WHERE id = ?", [$input['user_id']]);
            if (!$existing_user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            // Hash new password
            $new_password_hash = $this->auth->hashPassword($input['new_password']);
            
            // Update password
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $result = $this->db->execute($sql, [$new_password_hash, $input['user_id']]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => null,
                    'message' => 'Password reset successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to reset password'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Password reset failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user profile
     */
    private function getProfile() {
        try {
            // Check if user is logged in
            if (!$this->auth->isLoggedIn()) {
                return [
                    'success' => false,
                    'message' => 'Authentication required'
                ];
            }
            
            $current_user = $this->auth->getCurrentUser();
            
            // Get full user data
            $sql = "SELECT id, username, email, role, created_at, updated_at FROM users WHERE id = ?";
            $user_data = $this->db->querySingle($sql, [$current_user['id']]);
            
            if ($user_data) {
                return [
                    'success' => true,
                    'data' => $user_data,
                    'message' => 'Profile retrieved successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve profile: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check authentication status
     */
    private function checkAuth() {
        try {
            if ($this->auth->isLoggedIn()) {
                $current_user = $this->auth->getCurrentUser();
                return [
                    'success' => true,
                    'data' => [
                        'authenticated' => true,
                        'user' => $current_user
                    ],
                    'message' => 'User is authenticated'
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'authenticated' => false,
                        'user' => null
                    ],
                    'message' => 'User is not authenticated'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Authentication check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user permissions
     */
    private function getPermissions() {
        try {
            // Check if user is logged in
            if (!$this->auth->isLoggedIn()) {
                return [
                    'success' => false,
                    'message' => 'Authentication required'
                ];
            }
            
            $current_user = $this->auth->getCurrentUser();
            
            $permissions = [
                'is_admin' => $this->auth->isAdmin(),
                'is_moderator' => $this->auth->isModerator(),
                'can_manage_tournaments' => $this->auth->isModerator(),
                'can_manage_events' => $this->auth->isModerator(),
                'can_manage_committee' => $this->auth->isModerator(),
                'can_manage_gallery' => $this->auth->isModerator(),
                'can_manage_sponsors' => $this->auth->isModerator(),
                'can_manage_achievements' => $this->auth->isModerator(),
                'can_manage_settings' => $this->auth->isAdmin(),
                'can_manage_users' => $this->auth->isAdmin()
            ];
            
            return [
                'success' => true,
                'data' => $permissions,
                'message' => 'Permissions retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve permissions: ' . $e->getMessage()
            ];
        }
    }
}
?>
