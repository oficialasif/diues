<?php
// Load common authentication
require_once 'auth_common.php';

// Require admin authentication
$auth->requireAdmin();

$user = $auth->getCurrentUser();

// Get quick statistics
try {
    $stats = [
        'tournaments' => $database->querySingle("SELECT COUNT(*) as count FROM tournaments")['count'],
        'events' => $database->querySingle("SELECT COUNT(*) as count FROM events")['count'],
        'members' => $database->querySingle("SELECT COUNT(*) as count FROM committee_members WHERE is_current = 1")['count'],
        'gallery' => $database->querySingle("SELECT COUNT(*) as count FROM gallery")['count'],
        'sponsors' => $database->querySingle("SELECT COUNT(*) as count FROM sponsors WHERE is_active = 1")['count']
    ];
} catch (Exception $e) {
    $stats = ['tournaments' => 0, 'events' => 0, 'members' => 0, 'gallery' => 0, 'sponsors' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DIU Esports Community</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 bg-green-600 bg-opacity-20 border border-green-500 rounded-lg text-green-400">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="committee.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-users w-5"></i>
                    <span>Committee</span>
                </a>
                
                <a href="tournaments.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
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
                
                <a href="countdown.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-clock w-5"></i>
                    <span>Countdown</span>
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
                <h1 class="text-3xl font-orbitron font-bold text-white mb-2">Admin Dashboard</h1>
                <p class="text-gray-400">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-400">Last login</p>
                <p class="text-white"><?php echo date('M j, Y g:i A'); ?></p>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6 border border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200 text-sm font-medium">Tournaments</p>
                        <p class="text-3xl font-bold text-white"><?php echo $stats['tournaments']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-trophy text-2xl text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl p-6 border border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-200 text-sm font-medium">Events</p>
                        <p class="text-3xl font-bold text-white"><?php echo $stats['events']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-2xl text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-6 border border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-200 text-sm font-medium">Members</p>
                        <p class="text-3xl font-bold text-white"><?php echo $stats['members']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl p-6 border border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-200 text-sm font-medium">Gallery</p>
                        <p class="text-3xl font-bold text-white"><?php echo $stats['gallery']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-images text-2xl text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-xl p-6 border border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-200 text-sm font-medium">Sponsors</p>
                        <p class="text-3xl font-bold text-white"><?php echo $stats['sponsors']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <i class="fas fa-handshake text-2xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <h3 class="text-xl font-semibold text-white mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="tournaments.php?action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg text-center transition-colors duration-200">
                        <i class="fas fa-plus mb-2 block text-xl"></i>
                        Add Tournament
                    </a>
                    <a href="events.php?action=add" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg text-center transition-colors duration-200">
                        <i class="fas fa-plus mb-2 block text-xl"></i>
                        Add Event
                    </a>
                    <a href="committee.php?action=add" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg text-center transition-colors duration-200">
                        <i class="fas fa-user-plus mb-2 block text-xl"></i>
                        Add Member
                    </a>
                    <a href="gallery.php?action=add" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg text-center transition-colors duration-200">
                        <i class="fas fa-upload mb-2 block text-xl"></i>
                        Upload Media
                    </a>
                </div>
            </div>
            
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <h3 class="text-xl font-semibold text-white mb-4">Recent Activity</h3>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 text-sm">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-gray-300">New tournament "Valorant Championship" added</span>
                        <span class="text-gray-500 text-xs">2 hours ago</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span class="text-gray-300">Committee member profile updated</span>
                        <span class="text-gray-500 text-xs">5 hours ago</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                        <span class="text-gray-300">New gallery images uploaded</span>
                        <span class="text-gray-500 text-xs">1 day ago</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
            <h3 class="text-xl font-semibold text-white mb-4">System Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-database text-2xl text-green-400"></i>
                    </div>
                    <p class="text-green-400 font-semibold">Database</p>
                    <p class="text-gray-400 text-sm">Connected</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-server text-2xl text-blue-400"></i>
                    </div>
                    <p class="text-blue-400 font-semibold">Server</p>
                    <p class="text-gray-400 text-sm">Online</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shield-alt text-2xl text-purple-400"></i>
                    </div>
                    <p class="text-purple-400 font-semibold">Security</p>
                    <p class="text-gray-400 text-sm">Protected</p>
                </div>
            </div>
        </div>
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
    </script>
</body>
</html>
