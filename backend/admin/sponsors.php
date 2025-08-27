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
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $partnership_type = $_POST['partnership_type'] ?? '';
        $website_url = trim($_POST['website_url'] ?? '');
        $benefits = trim($_POST['benefits'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle logo upload
        $logo_url = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    $logo_url = 'uploads/logos/' . $filename;
                }
            }
        }
        
        if (empty($name) || empty($partnership_type)) {
            $error = 'Name and Partnership Type are required fields.';
        } else {
            try {
                if ($post_action === 'add') {
                    $sql = "INSERT INTO sponsors (name, logo_url, category, partnership_type, website_url, benefits, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $database->execute($sql, [$name, $logo_url, $category, $partnership_type, $website_url, $benefits, $is_active]);
                    $message = 'Sponsor added successfully!';
                    $action = 'list';
                } else {
                    $id = intval($_POST['id']);
                    $sql = "UPDATE sponsors SET name = ?, logo_url = ?, category = ?, partnership_type = ?, website_url = ?, benefits = ?, is_active = ? WHERE id = ?";
                    $database->execute($sql, [$name, $logo_url, $category, $partnership_type, $website_url, $benefits, $is_active, $id]);
                    $message = 'Sponsor updated successfully!';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($post_action === 'delete') {
        $id = intval($_POST['id']);
        try {
            $database->execute("DELETE FROM sponsors WHERE id = ?", [$id]);
            $message = 'Sponsor deleted successfully!';
            $action = 'list';
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get sponsors
$sponsors = [];
if ($action === 'list') {
    try {
        $sponsors = $database->queryAll("SELECT * FROM sponsors ORDER BY partnership_type, name ASC");
    } catch (Exception $e) {
        $error = 'Error loading sponsors: ' . $e->getMessage();
    }
}

// Get sponsor for editing
$edit_sponsor = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $edit_sponsor = $database->querySingle("SELECT * FROM sponsors WHERE id = ?", [intval($_GET['id'])]);
        if (!$edit_sponsor) {
            $error = 'Sponsor not found.';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = 'Error loading sponsor: ' . $e->getMessage();
        $action = 'list';
    }
}

// Partnership types
$partnership_types = ['platinum', 'gold', 'silver', 'bronze'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sponsors Management - DIU Esports Community</title>
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
                
                <a href="events.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-calendar-alt w-5"></i>
                    <span>Events</span>
                </a>
                
                <a href="gallery.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-green-400 rounded-lg transition-colors duration-200">
                    <i class="fas fa-images w-5"></i>
                    <span>Gallery</span>
                </a>
                
                <a href="sponsors.php" class="flex items-center space-x-3 px-4 py-3 bg-green-600 bg-opacity-20 border border-green-500 rounded-lg text-green-400">
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
                <h1 class="text-3xl font-orbitron font-bold text-white mb-2">Sponsors Management</h1>
                <p class="text-gray-400">Manage community sponsors and partnerships</p>
            </div>
            <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                <i class="fas fa-plus mr-2"></i>Add Sponsor
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
            <!-- Sponsors List -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-600">
                            <tr>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Logo</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Name</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Category</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Partnership</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Website</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Status</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sponsors)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 px-4 text-center text-gray-400">
                                        No sponsors found. <a href="?action=add" class="text-green-400 hover:text-green-300">Add the first sponsor</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sponsors as $sponsor): ?>
                                    <tr class="border-b border-gray-700 hover:bg-gray-700 hover:bg-opacity-30">
                                        <td class="py-4 px-4">
                                            <?php if ($sponsor['logo_url']): ?>
                                                <img src="../<?php echo htmlspecialchars($sponsor['logo_url']); ?>" alt="<?php echo htmlspecialchars($sponsor['name']); ?>" class="w-16 h-12 object-contain">
                                            <?php else: ?>
                                                <div class="w-16 h-12 bg-gray-600 rounded flex items-center justify-center">
                                                    <i class="fas fa-building text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 font-medium"><?php echo htmlspecialchars($sponsor['name']); ?></td>
                                        <td class="py-4 px-4"><?php echo htmlspecialchars($sponsor['category']); ?></td>
                                        <td class="py-4 px-4">
                                            <?php
                                            $type_colors = [
                                                'platinum' => 'bg-gray-400 text-gray-900',
                                                'gold' => 'bg-yellow-500 text-yellow-900',
                                                'silver' => 'bg-gray-300 text-gray-900',
                                                'bronze' => 'bg-orange-600 text-orange-100'
                                            ];
                                            $type_color = $type_colors[$sponsor['partnership_type']] ?? 'bg-gray-600 text-gray-200';
                                            ?>
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $type_color; ?>">
                                                <?php echo ucfirst($sponsor['partnership_type']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($sponsor['website_url']): ?>
                                                <a href="<?php echo htmlspecialchars($sponsor['website_url']); ?>" target="_blank" class="text-blue-400 hover:text-blue-300">
                                                    <i class="fas fa-external-link-alt"></i> Visit
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($sponsor['is_active']): ?>
                                                <span class="bg-green-600 text-green-200 px-2 py-1 rounded-full text-xs">Active</span>
                                            <?php else: ?>
                                                <span class="bg-red-600 text-red-200 px-2 py-1 rounded-full text-xs">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <a href="?action=edit&id=<?php echo $sponsor['id']; ?>" class="text-blue-400 hover:text-blue-300">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this sponsor?')">
                                                    <input type="hidden" name="id" value="<?php echo $sponsor['id']; ?>">
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
                    <?php echo $action === 'add' ? 'Add New Sponsor' : 'Edit Sponsor'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_sponsor['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Sponsor Name *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($edit_sponsor['name'] ?? ''); ?>"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="partnership_type" class="block text-sm font-medium text-gray-300 mb-2">Partnership Type *</label>
                            <select id="partnership_type" name="partnership_type" required class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <option value="">Select partnership type</option>
                                <?php foreach ($partnership_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($edit_sponsor['partnership_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                            <input type="text" id="category" name="category"
                                   value="<?php echo htmlspecialchars($edit_sponsor['category'] ?? ''); ?>"
                                   placeholder="e.g., Gaming, Technology, Food"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="website_url" class="block text-sm font-medium text-gray-300 mb-2">Website URL</label>
                            <input type="url" id="website_url" name="website_url"
                                   value="<?php echo htmlspecialchars($edit_sponsor['website_url'] ?? ''); ?>"
                                   placeholder="https://example.com"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   <?php echo ($edit_sponsor['is_active'] ?? 1) ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-green-600 bg-gray-700 border-gray-600 rounded focus:ring-green-500">
                            <label for="is_active" class="ml-2 text-sm text-gray-300">Active Sponsor</label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="benefits" class="block text-sm font-medium text-gray-300 mb-2">Benefits & Perks</label>
                        <textarea id="benefits" name="benefits" rows="4" placeholder="Describe the benefits and perks for this sponsor..."
                                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500"><?php echo htmlspecialchars($edit_sponsor['benefits'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Sponsor Logo</label>
                        <input type="file" id="logo" name="logo" accept="image/*"
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                        <p class="text-sm text-gray-400 mt-1">Recommended: 300x200 pixels, JPG/PNG/GIF/SVG</p>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                            <?php echo $action === 'add' ? 'Add Sponsor' : 'Update Sponsor'; ?>
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
