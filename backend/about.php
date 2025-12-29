<?php
/**
 * About Handler Class
 * Handles about section content operations
 */

class AboutHandler {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = new Database();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Get all about content
     */
    public function getAll() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM about_content ORDER BY id ASC");
            $content = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by section for easier frontend consumption
            $formattedContent = [];
            foreach ($content as $item) {
                // If section is 'values', we might have multiple items
                if ($item['section_name'] === 'values') {
                     if (!isset($formattedContent['values'])) {
                         $formattedContent['values'] = [];
                     }
                     $formattedContent['values'][] = $item;
                } else {
                    // For single sections like 'mission' or 'vision'
                    $formattedContent[$item['section_name']] = $item;
                }
            }

            apiResponse([
                'success' => true,
                'data' => $formattedContent,
                'raw' => $content
            ]);
        } catch (Exception $e) {
            apiError($e->getMessage(), 500);
        }
    }
}
?>
