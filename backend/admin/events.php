<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$database = new Database();
$auth = new Auth($database);

// Require admin authentication
$auth->requireAdmin();

$user = $auth->getCurrentUser();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get action from POST data for form submissions
    $post_action = $_POST['action'] ?? '';
    
    if ($post_action === 'add' || $post_action === 'edit') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $event_type = $_POST['event_type'] ?? '';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
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
        
        if (empty($title) || empty($event_date) || empty($event_type)) {
            $error = 'Title, Event Date, and Event Type are required fields.';
        } elseif (strtotime($event_date) < time()) {
            $error = 'Event date cannot be in the past.';
        } else {
            try {
                if ($post_action === 'add') {
                    $sql = "INSERT INTO events (title, description, poster_url, event_date, location, event_type, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $database->execute($sql, [$title, $description, $poster_url, $event_date, $location, $event_type, $is_featured, $status]);
                    $message = 'Event added successfully!';
                    $action = 'list';
                } else {
                    $id = intval($_POST['id']);
                    $sql = "UPDATE events SET title = ?, description = ?, poster_url = ?, event_date = ?, location = ?, event_type = ?, is_featured = ?, status = ? WHERE id = ?";
                    $database->execute($sql, [$title, $description, $poster_url, $event_date, $location, $event_type, $is_featured, $status, $id]);
                    $message = 'Event updated successfully!';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($post_action === 'delete') {
        $id = intval($_POST['id']);
        try {
            // Check if event exists first
            $existing = $database->querySingle("SELECT id, title FROM events WHERE id = ?", [$id]);
            if (!$existing) {
                $error = 'Event not found.';
            } else {
                $result = $database->execute("DELETE FROM events WHERE id = ?", [$id]);
                if ($result !== false) {
                    $message = 'Event "' . htmlspecialchars($existing['title']) . '" deleted successfully!';
                    $action = 'list';
                } else {
                    $error = 'Failed to delete event.';
                }
            }
        } catch (Exception $e) {
            $error = 'Error deleting event: ' . $e->getMessage();
        }
    }
}

// Get events
$events = [];
if ($action === 'list') {
    try {
        $events = $database->queryAll("SELECT * FROM events ORDER BY event_date DESC, created_at DESC");
    } catch (Exception $e) {
        $error = 'Error loading events: ' . $e->getMessage();
    }
}

// Get event for editing
$edit_event = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $edit_event = $database->querySingle("SELECT * FROM events WHERE id = ?", [intval($_GET['id'])]);
        if (!$edit_event) {
            $error = 'Event not found.';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = 'Error loading event: ' . $e->getMessage();
        $action = 'list';
    }
}

// Event types
$event_types = ['tournament', 'meetup', 'workshop', 'celebration'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - DIU Esports Community</title>
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
                
                <a href="tournaments.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-trophy w-5"></i>
                    <span>Tournaments</span>
                </a>
                
                <a href="events.php" class="flex items-center space-x-3 px-4 py-3 bg-green-600 bg-opacity-20 border border-green-500 rounded-lg text-green-400">
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
                <h1 class="text-3xl font-orbitron font-bold text-white mb-2">Events Management</h1>
                <p class="text-gray-400">Manage community events and activities</p>
            </div>
            <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                <i class="fas fa-plus mr-2"></i>Add Event
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
            <!-- Events List -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-600">
                            <tr>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Poster</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Title</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Type</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Date & Time</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Location</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Status</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 px-4 text-center text-gray-400">
                                        No events found. <a href="?action=add" class="text-green-400 hover:text-green-300">Add the first event</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <tr class="border-b border-gray-700 hover:bg-gray-700 hover:bg-opacity-30">
                                        <td class="py-4 px-4">
                                            <?php if ($event['poster_url']): ?>
                                                <img src="../<?php echo htmlspecialchars($event['poster_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-16 h-12 object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-16 h-12 bg-gray-600 rounded flex items-center justify-center">
                                                    <i class="fas fa-calendar text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-medium"><?php echo htmlspecialchars($event['title']); ?></div>
                                            <div class="text-sm text-gray-400"><?php echo htmlspecialchars(substr($event['description'], 0, 50)) . (strlen($event['description']) > 50 ? '...' : ''); ?></div>
                                            <?php if ($event['is_featured']): ?>
                                                <span class="inline-block bg-yellow-600 text-yellow-200 px-2 py-1 rounded-full text-xs mt-1">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="px-2 py-1 rounded-full text-xs bg-blue-600 text-blue-200">
                                                <?php echo ucfirst($event['event_type']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-sm">
                                                <div><?php echo date('M j, Y', strtotime($event['event_date'])); ?></div>
                                                <div class="text-gray-400"><?php echo date('g:i A', strtotime($event['event_date'])); ?></div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php echo $event['location'] ? htmlspecialchars($event['location']) : '<span class="text-gray-400">TBA</span>'; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php
                                            $status_colors = [
                                                'upcoming' => 'bg-blue-600 text-blue-200',
                                                'ongoing' => 'bg-green-600 text-green-200',
                                                'completed' => 'bg-gray-600 text-gray-200',
                                                'cancelled' => 'bg-red-600 text-red-200'
                                            ];
                                            $status_color = $status_colors[$event['status']] ?? 'bg-gray-600 text-gray-200';
                                            ?>
                                            <span class="px-2 py-1 rounded-full text-xs <?php echo $status_color; ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <a href="?action=edit&id=<?php echo $event['id']; ?>" class="text-blue-400 hover:text-blue-300">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="text-red-400 hover:text-red-300">
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
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <h2 class="text-2xl font-semibold text-white mb-6">
                    <?php echo $action === 'add' ? 'Add New Event' : 'Edit Event'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_event['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Event Title *</label>
                            <input type="text" id="title" name="title" required
                                   value="<?php echo htmlspecialchars($edit_event['title'] ?? ''); ?>"
                                   placeholder="e.g., Esports Meetup 2024"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="event_type" class="block text-sm font-medium text-gray-300 mb-2">Event Type *</label>
                            <select id="event_type" name="event_type" required class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <option value="">Select event type</option>
                                <?php foreach ($event_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($edit_event['event_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="event_date" class="block text-sm font-medium text-gray-300 mb-2">Event Date & Time *</label>
                            <input type="datetime-local" id="event_date" name="event_date" required
                                   value="<?php echo $edit_event['event_date'] ? date('Y-m-d\TH:i', strtotime($edit_event['event_date'])) : ''; ?>"
                                   min="<?php echo date('Y-m-d\TH:i'); ?>"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-300 mb-2">Location</label>
                            <input type="text" id="location" name="location"
                                   value="<?php echo htmlspecialchars($edit_event['location'] ?? ''); ?>"
                                   placeholder="e.g., DIU Campus, Room 301"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                            <select id="status" name="status" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <option value="upcoming" <?php echo ($edit_event['status'] ?? 'upcoming') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="ongoing" <?php echo ($edit_event['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="completed" <?php echo ($edit_event['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($edit_event['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                   <?php echo ($edit_event['is_featured'] ?? 0) ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-green-600 bg-gray-700 border-gray-600 rounded focus:ring-green-500">
                            <label for="is_featured" class="ml-2 text-sm text-gray-300">Featured Event</label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe the event, agenda, and what to expect..."
                                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500"><?php echo htmlspecialchars($edit_event['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="poster" class="block text-sm font-medium text-gray-300 mb-2">Event Poster</label>
                        <input type="file" id="poster" name="poster" accept="image/*"
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                        <p class="text-sm text-gray-400 mt-1">Recommended: 800x600 pixels, JPG/PNG/GIF</p>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                            <?php echo $action === 'add' ? 'Add Event' : 'Update Event'; ?>
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
    </script>
</body>
</html>
