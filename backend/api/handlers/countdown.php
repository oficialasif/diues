<?php
/**
 * Event Countdown Settings API Handler
 * Allows admins to manage countdown timer display settings
 */

class CountdownHandler {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Get current countdown settings
     */
    public function get() {
        try {
            $sql = "SELECT * FROM event_countdown_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$settings) {
                // Return default settings if none exist
                return [
                    'success' => true,
                    'data' => [
                        'status_text' => 'Starting soon!',
                        'custom_message' => 'Get ready for our next exciting event!',
                        'target_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
                        'show_countdown' => true,
                        'countdown_type' => 'days'
                    ],
                    'message' => 'Default countdown settings retrieved'
                ];
            }

            return [
                'success' => true,
                'data' => $settings,
                'message' => 'Countdown settings retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve countdown settings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update countdown settings
     */
    public function update($data) {
        try {
            $requiredFields = ['status_text', 'target_date', 'show_countdown'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ];
                }
            }

            // Deactivate all existing settings
            $stmt = $this->db->prepare("UPDATE event_countdown_settings SET is_active = 0");
            $stmt->execute();

            // Insert new settings
            $sql = "INSERT INTO event_countdown_settings (status_text, custom_message, target_date, is_active, show_countdown, countdown_type) VALUES (?, ?, ?, 1, ?, ?)";
            $params = [
                $data['status_text'],
                $data['custom_message'] ?? '',
                $data['target_date'],
                $data['show_countdown'] ? 1 : 0,
                $data['countdown_type'] ?? 'days'
            ];

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Countdown settings updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update countdown settings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all countdown settings (for frontend)
     */
    public function getAll() {
        try {
            // Return the active countdown setting for frontend
            $sql = "SELECT * FROM event_countdown_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$settings) {
                // Return null data if no active countdown exists
                return [
                    'success' => true,
                    'data' => null,
                    'message' => 'No active countdown settings found'
                ];
            }

            return [
                'success' => true,
                'data' => $settings,
                'message' => 'Active countdown settings retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve countdown settings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all countdown settings for admin panel
     */
    public function getAdminAll() {
        try {
            $sql = "SELECT * FROM event_countdown_settings ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $settings,
                'message' => 'All countdown settings retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve countdown settings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete countdown setting
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM event_countdown_settings WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return [
                'success' => true,
                'message' => 'Countdown setting deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete countdown setting: ' . $e->getMessage()
            ];
        }
    }
}
?>
