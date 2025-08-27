<?php
/**
 * Settings API Handler
 * Manages all site settings and configuration options
 */

class SettingsHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all settings
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM site_settings ORDER BY setting_key ASC";
            $settings = $this->db->queryAll($sql);
            
            // Convert to key-value pairs for easier use
            $settings_array = [];
            foreach ($settings as $setting) {
                $settings_array[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return [
                'success' => true,
                'data' => $settings_array,
                'message' => 'Settings retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve settings: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific setting by key
     */
    public function get($key) {
        try {
            $sql = "SELECT * FROM site_settings WHERE setting_key = ?";
            $setting = $this->db->querySingle($sql, [$key]);
            
            if (!$setting) {
                return [
                    'success' => false,
                    'message' => 'Setting not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $setting,
                'message' => 'Setting retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve setting: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new setting
     */
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['setting_key', 'setting_value'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' is required"
                    ];
                }
            }
            
            // Check if setting already exists
            $existing = $this->db->querySingle("SELECT id FROM site_settings WHERE setting_key = ?", [$input['setting_key']]);
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'Setting key already exists'
                ];
            }
            
            $sql = "INSERT INTO site_settings (setting_key, setting_value, description) VALUES (?, ?, ?)";
            
            $params = [
                $input['setting_key'],
                $input['setting_value'],
                $input['description'] ?? ''
            ];
            
            $setting_id = $this->db->execute($sql, $params);
            
            if ($setting_id) {
                return [
                    'success' => true,
                    'data' => ['id' => $setting_id],
                    'message' => 'Setting created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create setting'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create setting: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing setting
     */
    public function update($key) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if setting exists
            $existing = $this->db->querySingle("SELECT id FROM site_settings WHERE setting_key = ?", [$key]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Setting not found'
                ];
            }
            
            // Build update query dynamically
            $updates = [];
            $params = [];
            
            $fields = ['setting_value', 'description'];
            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $params[] = $key; // Add key for WHERE clause
            
            $sql = "UPDATE site_settings SET " . implode(', ', $updates) . " WHERE setting_key = ?";
            $result = $this->db->execute($sql, $params);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['key' => $key],
                    'message' => 'Setting updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update setting'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a setting
     */
    public function delete($key) {
        try {
            // Check if setting exists
            $existing = $this->db->querySingle("SELECT id FROM site_settings WHERE setting_key = ?", [$key]);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Setting not found'
                ];
            }
            
            // Don't allow deletion of critical settings
            $critical_settings = ['site_title', 'site_description', 'contact_email'];
            if (in_array($key, $critical_settings)) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete critical setting'
                ];
            }
            
            // Delete setting
            $sql = "DELETE FROM site_settings WHERE setting_key = ?";
            $result = $this->db->execute($sql, [$key]);
            
            if ($result !== false) {
                return [
                    'success' => true,
                    'data' => ['key' => $key],
                    'message' => 'Setting deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete setting'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete setting: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get settings by action
     */
    public function getAction($id, $action) {
        switch ($action) {
            case 'contact':
                return $this->getContactSettings();
            case 'social':
                return $this->getSocialSettings();
            case 'site':
                return $this->getSiteSettings();
            case 'reset-defaults':
                return $this->resetToDefaults();
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
        }
    }
    
    /**
     * Get contact-related settings
     */
    private function getContactSettings() {
        try {
            $sql = "SELECT * FROM site_settings WHERE setting_key LIKE 'contact_%' ORDER BY setting_key ASC";
            $settings = $this->db->queryAll($sql);
            
            $contact_settings = [];
            foreach ($settings as $setting) {
                $contact_settings[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return [
                'success' => true,
                'data' => $contact_settings,
                'message' => 'Contact settings retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve contact settings: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get social media settings
     */
    private function getSocialSettings() {
        try {
            $sql = "SELECT * FROM site_settings WHERE setting_key LIKE 'social_%' ORDER BY setting_key ASC";
            $settings = $this->db->queryAll($sql);
            
            $social_settings = [];
            foreach ($settings as $setting) {
                $social_settings[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return [
                'success' => true,
                'data' => $social_settings,
                'message' => 'Social settings retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve social settings: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get site configuration settings
     */
    private function getSiteSettings() {
        try {
            $sql = "SELECT * FROM site_settings WHERE setting_key LIKE 'site_%' ORDER BY setting_key ASC";
            $settings = $this->db->queryAll($sql);
            
            $site_settings = [];
            foreach ($settings as $setting) {
                $site_settings[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return [
                'success' => true,
                'data' => $site_settings,
                'message' => 'Site settings retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve site settings: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Reset settings to defaults
     */
    private function resetToDefaults() {
        try {
            $default_settings = [
                'site_title' => 'DIU ESPORTS COMMUNITY',
                'site_description' => 'Professional esports community at Daffodil International University',
                'contact_email' => 'esports@diu.edu.bd',
                'contact_phone' => '+880 1234-567890',
                'address' => 'Daffodil International University, Dhaka, Bangladesh',
                'social_discord' => 'https://discord.gg/diuesports',
                'social_twitch' => 'https://twitch.tv/diuesports',
                'social_facebook' => 'https://facebook.com/diuesports',
                'social_youtube' => 'https://youtube.com/diuesports'
            ];
            
            foreach ($default_settings as $key => $value) {
                $sql = "INSERT INTO site_settings (setting_key, setting_value, description) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                
                $description = $this->getDefaultDescription($key);
                $this->db->execute($sql, [$key, $value, $description]);
            }
            
            return [
                'success' => true,
                'data' => $default_settings,
                'message' => 'Settings reset to defaults successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get default description for a setting key
     */
    private function getDefaultDescription($key) {
        $descriptions = [
            'site_title' => 'Main website title',
            'site_description' => 'Website description',
            'contact_email' => 'Primary contact email',
            'contact_phone' => 'Primary contact phone',
            'address' => 'Physical address',
            'social_discord' => 'Discord server link',
            'social_twitch' => 'Twitch channel',
            'social_facebook' => 'Facebook page',
            'social_youtube' => 'YouTube channel'
        ];
        
        return $descriptions[$key] ?? '';
    }
}
?>
