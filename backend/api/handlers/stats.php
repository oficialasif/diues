<?php
/**
 * Stats API Handler
 * Provides real-time statistics for the frontend
 */

class StatsHandler {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all statistics
     */
    public function getAll() {
        try {
            // Get tournament count
            $tournamentsCount = $this->db->querySingle("SELECT COUNT(*) as count FROM tournaments")['count'];
            
            // Get total players (sum of current_participants from all tournaments)
            $playersCount = $this->db->querySingle("SELECT COALESCE(SUM(current_participants), 0) as count FROM tournaments")['count'];
            
            // Get games count
            $gamesCount = $this->db->querySingle("SELECT COUNT(*) as count FROM games")['count'];
            
            // If no games in database, use a default value
            if ($gamesCount == 0) {
                $gamesCount = 7; // Default games count
            }
            
            // Get events count
            $eventsCount = $this->db->querySingle("SELECT COUNT(*) as count FROM events")['count'];
            
            // Get committee members count
            $membersCount = $this->db->querySingle("SELECT COUNT(*) as count FROM committee_members WHERE is_current = 1")['count'];
            
            // Get gallery items count
            $galleryCount = $this->db->querySingle("SELECT COUNT(*) as count FROM gallery")['count'];
            
            // Get sponsors count
            $sponsorsCount = $this->db->querySingle("SELECT COUNT(*) as count FROM sponsors WHERE is_active = 1")['count'];
            
            $stats = [
                'tournaments' => (int)$tournamentsCount,
                'players' => (int)$playersCount,
                'games' => (int)$gamesCount,
                'events' => (int)$eventsCount,
                'members' => (int)$membersCount,
                'gallery' => (int)$galleryCount,
                'sponsors' => (int)$sponsorsCount
            ];
            
            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get specific statistic
     */
    public function get($stat) {
        try {
            $validStats = ['tournaments', 'players', 'games', 'events', 'members', 'gallery', 'sponsors'];
            
            if (!in_array($stat, $validStats)) {
                return [
                    'success' => false,
                    'message' => 'Invalid statistic requested'
                ];
            }
            
            $value = 0;
            
            switch ($stat) {
                case 'tournaments':
                    $value = $this->db->querySingle("SELECT COUNT(*) as count FROM tournaments")['count'];
                    break;
                case 'players':
                    $value = $this->db->querySingle("SELECT COALESCE(SUM(current_participants), 0) as count FROM tournaments")['count'];
                    break;
                case 'games':
                    $value = $this->db->querySingle("SELECT COUNT(*) as count FROM games")['count'];
                    if ($value == 0) $value = 7; // Default
                    break;
                case 'events':
                    $value = $this->db->querySingle("SELECT COUNT(*) as count FROM events")['count'];
                    break;
                case 'members':
                    $value = $this->db->querySingle("SELECT COUNT(*) as count FROM committee_members WHERE is_current = 1")['count'];
                    break;
                case 'gallery':
                    $value = $this->db->querySingle("SELECT COUNT(*) as count FROM gallery")['count'];
                    break;
                case 'sponsors':
                    $value = $this->db->querySingle("SELECT COUNT(*) as count FROM sponsors WHERE is_active = 1")['count'];
                    break;
            }
            
            return [
                'success' => true,
                'data' => (int)$value,
                'message' => ucfirst($stat) . ' count retrieved successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve ' . $stat . ' count: ' . $e->getMessage()
            ];
        }
    }
}
?>
