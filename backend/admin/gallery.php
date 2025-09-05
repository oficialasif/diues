<?php
// Load common authentication
require_once 'auth_common.php';

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
        $category = $_POST['category'] ?? '';
        $year = intval($_POST['year'] ?? date('Y'));
        $is_featured = isset($_POST['is_featured']) ? true : false;
        $tags_input = trim($_POST['tags'] ?? '');
        // Convert tags string to JSON array for PostgreSQL JSONB field
        $tags = $tags_input ? json_encode(explode(',', $tags_input)) : null;
        
        // Handle image upload
        $image_url = '';
        $has_new_image = false;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $has_new_image = true;
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                // Upload to Cloudinary
                if (file_exists('../services/CloudinaryService.php')) {
                    require_once '../services/CloudinaryService.php';
                    $cloudinary = new CloudinaryService();
                } else {
                    // Fallback to simple version
                    require_once '../services/CloudinaryServiceSimple.php';
                    $cloudinary = new CloudinaryServiceSimple();
                }
                
                $uploadResult = $cloudinary->uploadFromFile($_FILES['image'], 'diu-esports/gallery');
                
                if ($uploadResult['success']) {
                    $image_url = $uploadResult['public_id']; // Store public_id instead of local path
                } else {
                    $error = 'Failed to upload image: ' . $uploadResult['error'];
                }
            } else {
                $error = 'Invalid file type. Please upload JPG, PNG, or GIF images.';
            }
        } elseif ($post_action === 'edit') {
            // For edit, keep existing image if no new image uploaded
            $id = intval($_POST['id']);
            $existing = $database->query("SELECT image_url FROM gallery WHERE id = ?", [$id])->fetch();
            if ($existing) {
                $image_url = $existing['image_url'];
            }
        }
        
        // Validation: Check if we have required fields
        if (empty($title) || empty($category)) {
            $error = 'Title and Category are required fields.';
        } elseif ($post_action === 'add' && empty($image_url)) {
            $error = 'Image is required when adding a new gallery item.';
        } elseif ($post_action === 'edit' && empty($image_url)) {
            // For edit, if no new image uploaded, we should have kept the existing one
            // If we reach here, it means there was no existing image either
            $error = 'No image found. Please upload an image.';
        } else {
            try {
                if ($post_action === 'add') {
                    $sql = "INSERT INTO gallery (title, description, image_url, video_url, category, year, is_featured, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $database->execute($sql, [$title, $description, $image_url, null, $category, $year, $is_featured, $tags]);
                    $message = 'Gallery item added successfully!';
                    $action = 'list';
                } else {
                    $id = intval($_POST['id']);
                    $sql = "UPDATE gallery SET title = ?, description = ?, image_url = ?, video_url = ?, category = ?, year = ?, is_featured = ?, tags = ? WHERE id = ?";
                    $database->execute($sql, [$title, $description, $image_url, null, $category, $year, $is_featured, $tags, $id]);
                    $message = 'Gallery item updated successfully!';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($post_action === 'delete') {
        $id = intval($_POST['id']);
        try {
            $database->execute("DELETE FROM gallery WHERE id = ?", [$id]);
            $message = 'Gallery item deleted successfully!';
            $action = 'list';
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get gallery items
$gallery_items = [];
if ($action === 'list') {
    try {
        $gallery_items = $database->queryAll("SELECT * FROM gallery ORDER BY year DESC, created_at DESC");
    } catch (Exception $e) {
        $error = 'Error loading gallery: ' . $e->getMessage();
    }
}

// Get item for editing
$edit_item = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $edit_item = $database->querySingle("SELECT * FROM gallery WHERE id = ?", [intval($_GET['id'])]);
        if (!$edit_item) {
            $error = 'Gallery item not found.';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = 'Error loading item: ' . $e->getMessage();
        $action = 'list';
    }
}

// Categories
$categories = ['tournament', 'event', 'achievement', 'community'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - DIU Esports Community</title>
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
                
                <a href="gallery.php" class="flex items-center space-x-3 px-4 py-3 bg-green-600 bg-opacity-20 border border-green-500 rounded-lg text-green-400">
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
                <h1 class="text-3xl font-orbitron font-bold text-white mb-2">Gallery Management</h1>
                <p class="text-gray-400">Manage community photos and media</p>
            </div>
            <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                <i class="fas fa-plus mr-2"></i>Add Media
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
            <!-- Gallery Grid -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <?php if (empty($gallery_items)): ?>
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-images"></i>
                        </div>
                        <p class="text-gray-400 text-lg mb-4">No gallery items found</p>
                        <a href="?action=add" class="text-green-400 hover:text-green-300">Add the first media item</a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($gallery_items as $item): ?>
                            <div class="bg-gray-700 rounded-lg overflow-hidden hover:transform hover:scale-105 transition-all duration-300">
                                <div class="relative">
                                    <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         class="w-full h-48 object-cover">
                                    
                                    <?php if ($item['is_featured']): ?>
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-yellow-600 text-yellow-200 px-2 py-1 rounded-full text-xs">Featured</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-blue-600 text-blue-200 px-2 py-1 rounded-full text-xs">
                                            <?php echo ucfirst($item['category']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <h3 class="font-semibold text-white mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p class="text-gray-400 text-sm mb-3">
                                        <?php echo htmlspecialchars(substr($item['description'], 0, 80)) . (strlen($item['description']) > 80 ? '...' : ''); ?>
                                    </p>
                                    
                                    <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                                        <span><?php echo $item['year']; ?></span>
                                        <?php if ($item['tags']): ?>
                                            <span class="text-green-400"><?php echo htmlspecialchars($item['tags']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" class="text-blue-400 hover:text-blue-300">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="text-red-400 hover:text-red-300">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <h2 class="text-2xl font-semibold text-white mb-6">
                    <?php echo $action === 'add' ? 'Add New Media' : 'Edit Media'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                            <input type="text" id="title" name="title" required
                                   value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
                            <select id="category" name="category" required class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($edit_item['category'] ?? '') === $cat ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-300 mb-2">Year</label>
                            <select id="year" name="year" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($edit_item['year'] ?? date('Y')) == $y ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                   <?php echo ($edit_item['is_featured'] ?? 0) ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-green-600 bg-gray-700 border-gray-600 rounded focus:ring-green-500">
                            <label for="is_featured" class="ml-2 text-sm text-gray-300">Featured Item</label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe the media content..."
                                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500"><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-300 mb-2">Tags</label>
                        <input type="text" id="tags" name="tags"
                               value="<?php echo htmlspecialchars($edit_item['tags'] ?? ''); ?>"
                               placeholder="e.g., valorant, tournament, winners"
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        <p class="text-sm text-gray-400 mt-1">Separate tags with commas</p>
                    </div>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-300 mb-2">Image *</label>
                        <input type="file" id="image" name="image" accept="image/*" required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                        <p class="text-sm text-gray-400 mt-1">Recommended: 1200x800 pixels, JPG/PNG/GIF</p>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                            <?php echo $action === 'add' ? 'Add Media' : 'Update Media'; ?>
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
