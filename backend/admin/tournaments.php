<?php
// Load common authentication
require_once 'auth_common.php';

// Require admin authentication
$auth->requireAdmin();

$user = $auth->getCurrentUser();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Get tournament registrations if viewing registrations
$registrations = [];
$current_tournament = null;
if ($action === 'view_registrations' && isset($_GET['id'])) {
    try {
        $tournament_id = intval($_GET['id']);
        $current_tournament = $database->querySingle("
            SELECT t.*, g.name as game_name, g.genre 
            FROM tournaments t 
            LEFT JOIN games g ON t.game_id = g.id 
            WHERE t.id = ?
        ", [$tournament_id]);
        
        if ($current_tournament) {
            $registrations = $database->queryAll("
                SELECT tr.*, 
                       t.name as tournament_name,
                       t.game_id,
                       g.name as game_name,
                       g.genre
                FROM tournament_registrations tr
                JOIN tournaments t ON tr.tournament_id = t.id
                LEFT JOIN games g ON t.game_id = g.id
                WHERE tr.tournament_id = ?
                ORDER BY tr.registration_date DESC
            ", [$tournament_id]);
            
            // Get team members for each registration
            foreach ($registrations as &$registration) {
                $team_members = $database->queryAll("
                    SELECT * FROM tournament_team_members 
                    WHERE registration_id = ? 
                    ORDER BY player_role, id
                ", [$registration['id']]);
                $registration['team_members'] = $team_members;
            }
        } else {
            $error = 'Tournament not found.';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = 'Error loading registrations: ' . $e->getMessage();
        $action = 'list';
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log the POST data
    error_log("POST data received: " . print_r($_POST, true));
    
    // Get action from POST data for form submissions
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action === 'add' || $post_action === 'edit') {
        error_log("Processing " . $post_action . " operation");
        error_log("POST data for " . $post_action . ": " . print_r($_POST, true));
        
        $game_id = intval($_POST['game_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $prize_pool = floatval($_POST['prize_pool'] ?? 0);
        $max_participants = intval($_POST['max_participants'] ?? 0);
        $status = $_POST['status'] ?? 'upcoming';
        
        // Handle poster upload
        $poster_url = '';
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/posters/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['poster']['tmp_name'], $upload_path)) {
                    $poster_url = 'uploads/posters/' . $filename;
                }
            }
        }
        
        if (empty($name) || empty($start_date) || empty($end_date) || $game_id <= 0) {
            $error = 'Name, Game, Start Date, and End Date are required fields.';
        } elseif (strtotime($start_date) >= strtotime($end_date)) {
            $error = 'End date must be after start date.';
        } else {
            try {
                if ($post_action === 'add') {
                    $sql = "INSERT INTO tournaments (game_id, name, description, poster_url, start_date, end_date, prize_pool, max_participants, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $database->execute($sql, [$game_id, $name, $description, $poster_url, $start_date, $end_date, $prize_pool, $max_participants, $status]);
                    $message = 'Tournament added successfully!';
                    $action = 'list';
                } else {
                    $id = intval($_POST['id']);
                    $sql = "UPDATE tournaments SET game_id = ?, name = ?, description = ?, poster_url = ?, start_date = ?, end_date = ?, prize_pool = ?, max_participants = ?, status = ? WHERE id = ?";
                    $database->execute($sql, [$game_id, $name, $description, $poster_url, $start_date, $end_date, $prize_pool, $max_participants, $status, $id]);
                    $message = 'Tournament updated successfully!';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($post_action === 'delete') {
        $id = intval($_POST['id']);
        error_log("Attempting to delete tournament with ID: " . $id);
        try {
            // Check if tournament exists first
            $existing = $database->querySingle("SELECT id, name FROM tournaments WHERE id = ?", [$id]);
            if (!$existing) {
                $error = 'Tournament not found.';
                error_log("Tournament not found with ID: " . $id);
            } else {
                error_log("Found tournament: " . $existing['name'] . " (ID: " . $existing['id'] . ")");
                $result = $database->execute("DELETE FROM tournaments WHERE id = ?", [$id]);
                if ($result !== false) {
                    $message = 'Tournament "' . htmlspecialchars($existing['name']) . '" deleted successfully!';
                    $action = 'list';
                    error_log("Tournament deleted successfully: " . $existing['name']);
                } else {
                    $error = 'Failed to delete tournament.';
                    error_log("Failed to delete tournament: " . $existing['name']);
                }
            }
        } catch (Exception $e) {
            $error = 'Error deleting tournament: ' . $e->getMessage();
            error_log("Exception during tournament deletion: " . $e->getMessage());
        }
    }
}

// Get tournaments
$tournaments = [];
if ($action === 'list') {
    try {
        $tournaments = $database->queryAll("
            SELECT t.*, g.name as game_name 
            FROM tournaments t 
            LEFT JOIN games g ON t.game_id = g.id 
            ORDER BY t.start_date DESC, t.created_at DESC
        ");
    } catch (Exception $e) {
        $error = 'Error loading tournaments: ' . $e->getMessage();
    }
}

// Get games for dropdown
$games = [];
try {
    $games = $database->queryAll("SELECT id, name FROM games WHERE is_active = 1 ORDER BY name");
} catch (Exception $e) {
    $error = 'Error loading games: ' . $e->getMessage();
}

// Get tournament for editing
$edit_tournament = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $edit_tournament = $database->querySingle("SELECT * FROM tournaments WHERE id = ?", [intval($_GET['id'])]);
        if (!$edit_tournament) {
            $error = 'Tournament not found.';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = 'Error loading tournament: ' . $e->getMessage();
        $action = 'list';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournaments Management - DIU Esports Community</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .font-poppins { font-family: 'Poppins', sans-serif; }
        .neon-glow { box-shadow: 0 0 20px #22C55E, 0 0 40px #22C55E; }
        .cyber-bg { background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); }
        .neon-border { border: 2px solid #22C55E; }
        .neon-text { color: #22C55E; text-shadow: 0 0 10px #22C55E; }
        .sidebar { width: 280px; }
        .main-content { margin-left: 280px; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body class="cyber-bg min-h-screen font-poppins text-white">
    <!-- Sidebar -->
    <div class="sidebar fixed h-full bg-gray-900 bg-opacity-95 backdrop-blur-sm border-r border-gray-700">
        <div class="p-6">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-blue-600 to-green-500 rounded-full flex items-center justify-center neon-glow">
                    <span class="text-xl font-orbitron font-bold text-white">DIU</span>
                </div>
                <h2 class="text-lg font-orbitron font-bold text-white">ESPORTS ADMIN</h2>
            </div>
            
            <!-- User Info -->
            <div class="bg-gray-800 rounded-lg p-4 mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="text-xs text-gray-400"><?php echo ucfirst($user['role']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="committee.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-users w-5"></i>
                    <span>Committee</span>
                </a>
                
                <a href="tournaments.php" class="flex items-center space-x-3 px-4 py-3 bg-green-600 bg-opacity-20 border border-green-500 rounded-lg text-green-400">
                    <i class="fas fa-trophy w-5"></i>
                    <span>Tournaments</span>
                </a>
                
                <a href="events.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-calendar-alt w-5"></i>
                    <span>Events</span>
                </a>
                
                <a href="gallery.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-images w-5"></i>
                    <span>Gallery</span>
                </a>
                
                <a href="sponsors.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-handshake w-5"></i>
                    <span>Sponsors</span>
                </a>
                
                <a href="settings.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <!-- Logout -->
            <div class="mt-8 pt-6 border-t border-gray-700">
                <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 text-red-400 hover:bg-red-900 hover:bg-opacity-20 rounded-lg transition-colors duration-200">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-orbitron font-bold text-white mb-2">Tournaments Management</h1>
                <p class="text-gray-400">Manage esports tournaments and competitions</p>
            </div>
            <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                <i class="fas fa-plus mr-2"></i>Add Tournament
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Messages -->
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
        
        <?php if ($action === 'list'): ?>
            <!-- Tournaments List -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-600">
                            <tr>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Poster</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Name</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Game</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Dates</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Prize Pool</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Status</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tournaments)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 px-4 text-center text-gray-400">
                                        No tournaments found. <a href="?action=add" class="text-green-400 hover:text-green-300">Add the first tournament</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tournaments as $tournament): ?>
                                    <tr class="border-b border-gray-700 hover:bg-gray-700 hover:bg-opacity-30">
                                        <td class="py-4 px-4">
                                            <?php if ($tournament['poster_url']): ?>
                                                <img src="../<?php echo htmlspecialchars($tournament['poster_url']); ?>" alt="<?php echo htmlspecialchars($tournament['name']); ?>" class="w-16 h-12 object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-16 h-12 bg-gray-600 rounded flex items-center justify-center">
                                                    <i class="fas fa-trophy text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-medium"><?php echo htmlspecialchars($tournament['name']); ?></div>
                                            <div class="text-sm text-gray-400"><?php echo htmlspecialchars(substr($tournament['description'], 0, 50)) . (strlen($tournament['description']) > 50 ? '...' : ''); ?></div>
                                        </td>
                                        <td class="py-4 px-4"><?php echo htmlspecialchars($tournament['game_name'] ?? 'Unknown'); ?></td>
                                        <td class="py-4 px-4">
                                            <div class="text-sm">
                                                <div>Start: <?php echo date('M j, Y', strtotime($tournament['start_date'])); ?></div>
                                                <div>End: <?php echo date('M j, Y', strtotime($tournament['end_date'])); ?></div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($tournament['prize_pool'] > 0): ?>
                                                $<?php echo number_format($tournament['prize_pool'], 2); ?>
                                            <?php else: ?>
                                                <span class="text-gray-400">No prize</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php
                                            $status_colors = [
                                                'upcoming' => 'bg-blue-600 text-blue-200',
                                                'ongoing' => 'bg-green-600 text-green-200',
                                                'completed' => 'bg-gray-600 text-gray-200',
                                                'cancelled' => 'bg-red-600 text-red-200'
                                            ];
                                            $status_color = $status_colors[$tournament['status']] ?? 'bg-gray-600 text-gray-200';
                                            ?>
                                            <span class="px-2 py-1 rounded-full text-xs <?php echo $status_color; ?>">
                                                <?php echo ucfirst($tournament['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <a href="?action=view_registrations&id=<?php echo $tournament['id']; ?>" class="text-green-400 hover:text-green-300" title="View Registrations">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                                <a href="?action=edit&id=<?php echo $tournament['id']; ?>" class="text-blue-400 hover:text-blue-300" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this tournament?')" id="deleteForm_<?php echo $tournament['id']; ?>">
                                                    <input type="hidden" name="id" value="<?php echo $tournament['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="text-red-400 hover:text-red-300" onclick="console.log('Delete button clicked for tournament ID: <?php echo $tournament['id']; ?>')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($action === 'view_registrations'): ?>
            <!-- View Registrations -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-white mb-2">
                            Tournament Registrations
                        </h2>
                        <p class="text-gray-300">
                            <?php echo htmlspecialchars($current_tournament['name']); ?> - 
                            <?php echo htmlspecialchars($current_tournament['game_name']); ?> 
                            (<?php echo htmlspecialchars($current_tournament['genre']); ?>)
                        </p>
                    </div>
                    <a href="?action=list" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Tournaments
                    </a>
                </div>

                <?php if (empty($registrations)): ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📝</div>
                        <h3 class="text-xl font-semibold text-gray-300 mb-2">No Registrations Yet</h3>
                        <p class="text-gray-400">This tournament hasn't received any registrations yet.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-gray-700 rounded-lg overflow-hidden">
                            <thead class="bg-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Team</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Captain</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-600">
                                <?php foreach ($registrations as $registration): ?>
                                    <tr class="hover:bg-gray-600 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-white"><?php echo htmlspecialchars($registration['team_name']); ?></div>
                                            <div class="text-sm text-gray-400">
                                                <?php echo count($registration['team_members']); ?> member(s)
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full font-medium 
                                                <?php 
                                                echo ($registration['team_type'] === 'solo') ? 'bg-blue-900/30 text-blue-400 border border-blue-500' :
                                                     (($registration['team_type'] === 'duo') ? 'bg-green-900/30 text-green-400 border border-green-500' :
                                                     'bg-purple-900/30 text-purple-400 border border-purple-500');
                                                ?>">
                                                <?php echo ucfirst($registration['team_type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-white"><?php echo htmlspecialchars($registration['captain_name']); ?></div>
                                            <?php if ($registration['captain_student_id']): ?>
                                                <div class="text-sm text-gray-400">ID: <?php echo htmlspecialchars($registration['captain_student_id']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($registration['captain_department']): ?>
                                                <div class="text-sm text-gray-400"><?php echo htmlspecialchars($registration['captain_department']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="text-white"><?php echo htmlspecialchars($registration['captain_email']); ?></div>
                                                <?php if ($registration['captain_phone']): ?>
                                                    <div class="text-gray-400"><?php echo htmlspecialchars($registration['captain_phone']); ?></div>
                                                <?php endif; ?>
                                                <?php if ($registration['captain_discord']): ?>
                                                    <div class="text-gray-400">Discord: <?php echo htmlspecialchars($registration['captain_discord']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full font-medium 
                                                <?php 
                                                echo ($registration['status'] === 'pending') ? 'bg-yellow-900/30 text-yellow-400 border border-yellow-500' :
                                                     (($registration['status'] === 'approved') ? 'bg-green-900/30 text-green-400 border border-green-500' :
                                                     'bg-red-900/30 text-red-400 border border-red-500');
                                                ?>">
                                                <?php echo ucfirst($registration['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-300">
                                            <?php echo date('M j, Y', strtotime($registration['registration_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button 
                                                onclick="toggleTeamDetails(<?php echo $registration['id']; ?>)"
                                                class="text-blue-400 hover:text-blue-300 transition-colors"
                                                title="View Team Details"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Team Details Row -->
                                    <tr id="team-details-<?php echo $registration['id']; ?>" class="hidden bg-gray-800">
                                        <td colspan="7" class="px-6 py-4">
                                            <div class="space-y-4">
                                                <h4 class="font-semibold text-white">Team Members</h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <?php foreach ($registration['team_members'] as $member): ?>
                                                        <div class="bg-gray-700 rounded-lg p-3 border border-gray-600">
                                                            <div class="flex items-center justify-between mb-2">
                                                                <span class="font-medium text-white"><?php echo htmlspecialchars($member['player_name']); ?></span>
                                                                <span class="px-2 py-1 text-xs rounded-full 
                                                                    <?php 
                                                                    echo ($member['player_role'] === 'captain') ? 'bg-green-900/30 text-green-400 border border-green-500' :
                                                                         (($member['player_role'] === 'substitute') ? 'bg-orange-900/30 text-orange-400 border border-orange-500' :
                                                                         'bg-blue-900/30 text-blue-400 border border-blue-500');
                                                                    ?>">
                                                                    <?php echo ucfirst($member['player_role']); ?>
                                                                </span>
                                                            </div>
                                                            <?php if ($member['player_email']): ?>
                                                                <div class="text-sm text-gray-400"><?php echo htmlspecialchars($member['player_email']); ?></div>
                                                            <?php endif; ?>
                                                            <?php if ($member['player_phone']): ?>
                                                                <div class="text-sm text-gray-400"><?php echo htmlspecialchars($member['player_phone']); ?></div>
                                                            <?php endif; ?>
                                                            <?php if ($member['player_discord']): ?>
                                                                <div class="text-sm text-gray-400">Discord: <?php echo htmlspecialchars($member['player_discord']); ?></div>
                                                            <?php endif; ?>
                                                            <?php if ($member['player_student_id']): ?>
                                                                <div class="text-sm text-gray-400">ID: <?php echo htmlspecialchars($member['player_student_id']); ?></div>
                                                            <?php endif; ?>
                                                            <?php if ($member['player_department']): ?>
                                                                <div class="text-sm text-gray-400"><?php echo htmlspecialchars($member['player_department']); ?></div>
                                                            <?php endif; ?>
                                                            <?php if ($member['player_semester']): ?>
                                                                <div class="text-sm text-gray-400"><?php echo htmlspecialchars($member['player_semester']); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <h2 class="text-2xl font-semibold text-white mb-6">
                    <?php echo $action === 'add' ? 'Add New Tournament' : 'Edit Tournament'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_tournament['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Tournament Name *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($edit_tournament['name'] ?? ''); ?>"
                                   placeholder="e.g., Valorant Championship 2024"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="game_id" class="block text-sm font-medium text-gray-300 mb-2">Game *</label>
                            <select id="game_id" name="game_id" required class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <option value="">Select a game</option>
                                <?php foreach ($games as $game): ?>
                                    <option value="<?php echo $game['id']; ?>" <?php echo ($edit_tournament['game_id'] ?? '') == $game['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($game['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-300 mb-2">Start Date *</label>
                            <input type="date" id="start_date" name="start_date" required
                                   value="<?php echo $edit_tournament['start_date'] ?? ''; ?>"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-300 mb-2">End Date *</label>
                            <input type="date" id="end_date" name="end_date" required
                                   value="<?php echo $edit_tournament['end_date'] ?? ''; ?>"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="prize_pool" class="block text-sm font-medium text-gray-300 mb-2">Prize Pool ($)</label>
                            <input type="number" id="prize_pool" name="prize_pool" step="0.01" min="0"
                                   value="<?php echo $edit_tournament['prize_pool'] ?? ''; ?>"
                                   placeholder="0.00"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="max_participants" class="block text-sm font-medium text-gray-300 mb-2">Max Participants</label>
                            <input type="number" id="max_participants" name="max_participants" min="1"
                                   value="<?php echo $edit_tournament['max_participants'] ?? ''; ?>"
                                   placeholder="32"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                            <select id="status" name="status" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <option value="upcoming" <?php echo ($edit_tournament['status'] ?? 'upcoming') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="ongoing" <?php echo ($edit_tournament['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="completed" <?php echo ($edit_tournament['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($edit_tournament['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe the tournament, rules, and format..."
                                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500"><?php echo htmlspecialchars($edit_tournament['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="poster" class="block text-sm font-medium text-gray-300 mb-2">Tournament Poster</label>
                        <input type="file" id="poster" name="poster" accept="image/*"
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                        <p class="text-sm text-gray-400 mt-1">Recommended: 800x600 pixels, JPG/PNG/GIF</p>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                            <?php echo $action === 'add' ? 'Add Tournament' : 'Update Tournament'; ?>
                        </button>
                        <a href="?action=list" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Mobile Menu Toggle -->
    <div class="md:hidden fixed top-4 right-4 z-50">
        <button id="mobileMenuBtn" class="bg-gray-800 p-3 rounded-lg text-white">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        });
        
        // Date validation
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('end_date');
            if (startDate) {
                endDateInput.min = startDate;
            }
        });
        
        // Monitor delete form submissions
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    console.log('Form submitted:', this);
                    console.log('Form data:', new FormData(this));
                });
            });
        });
        
        // Toggle team details
        function toggleTeamDetails(registrationId) {
            const detailsRow = document.getElementById(`team-details-${registrationId}`);
            if (detailsRow) {
                detailsRow.classList.toggle('hidden');
            }
        }
    </script>
</body>
</html>
