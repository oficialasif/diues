<?php
require_once '../config/auth.php';
require_once '../config/database.php';

$auth = new Auth(new Database());
$auth->requireAdmin();

$database = new Database();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action === 'update') {
        $status_text = $_POST['status_text'] ?? '';
        $custom_message = $_POST['custom_message'] ?? '';
        $target_date = $_POST['target_date'] ?? '';
        $show_countdown = isset($_POST['show_countdown']) ? 1 : 0;
        $countdown_type = $_POST['countdown_type'] ?? 'days';
        
        if (empty($status_text) || empty($target_date)) {
            $error = 'Status text and target date are required';
        } else {
            try {
                $database->execute("UPDATE event_countdown_settings SET is_active = 0");
                
                $sql = "INSERT INTO event_countdown_settings (status_text, custom_message, target_date, is_active, show_countdown, countdown_type) VALUES (?, ?, ?, 1, ?, ?)";
                $database->execute($sql, [$status_text, $custom_message, $target_date, $show_countdown, $countdown_type]);
                $message = 'Countdown settings updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update countdown settings: ' . $e->getMessage();
            }
        }
    }
}

$currentSettings = null;
try {
    $currentSettings = $database->querySingle("SELECT * FROM event_countdown_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
} catch (Exception $e) {
    // Ignore error, will use defaults
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countdown Settings - DIU Esports Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .neon-border { border: 2px solid #22C55E; }
        .neon-glow { box-shadow: 0 0 20px #22C55E, 0 0 40px #22C55E; }
    </style>
</head>
<body class="bg-gray-900 min-h-screen text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-center">Event Countdown Settings</h1>
        
        <?php if ($message): ?>
            <div class="bg-green-900 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-900 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="bg-gray-800 rounded-2xl p-8 neon-border">
            <h2 class="text-2xl font-bold mb-6">Update Countdown Settings</h2>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Status Text *</label>
                        <input type="text" name="status_text" 
                               value="<?php echo htmlspecialchars($currentSettings['status_text'] ?? 'Starting soon!'); ?>" 
                               required
                               placeholder="e.g., Starting soon!, Coming soon!"
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Target Date & Time *</label>
                        <input type="datetime-local" name="target_date" 
                               value="<?php echo $currentSettings ? date('Y-m-d\TH:i', strtotime($currentSettings['target_date'])) : date('Y-m-d\TH:i', strtotime('+7 days')); ?>" 
                               required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Custom Message</label>
                    <textarea name="custom_message" rows="3"
                              placeholder="Optional motivational message..."
                              class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white"><?php echo htmlspecialchars($currentSettings['custom_message'] ?? ''); ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Countdown Display Type</label>
                        <select name="countdown_type" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white">
                            <option value="days" <?php echo ($currentSettings['countdown_type'] ?? 'days') === 'days' ? 'selected' : ''; ?>>Days</option>
                            <option value="hours" <?php echo ($currentSettings['countdown_type'] ?? 'days') === 'hours' ? 'selected' : ''; ?>>Hours</option>
                            <option value="minutes" <?php echo ($currentSettings['countdown_type'] ?? 'days') === 'minutes' ? 'selected' : ''; ?>>Minutes</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="show_countdown" 
                               <?php echo ($currentSettings['show_countdown'] ?? true) ? 'checked' : ''; ?>
                               class="w-5 h-5 text-green-600 bg-gray-700 border-gray-600 rounded">
                        <label class="ml-3 text-sm font-medium">Show countdown timer</label>
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg transition-colors neon-glow">
                        Update Countdown Settings
                    </button>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-8">
            <a href="dashboard.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
