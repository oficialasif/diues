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
        $name = trim($_POST['name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $achievements = trim($_POST['achievements'] ?? '');
        $year = intval($_POST['year'] ?? date('Y'));
        $is_current = isset($_POST['is_current']) ? 1 : 0;
        
        // Handle image upload - support multiple methods
        $image_url = '';
        
        // Method 1: Cloudinary URL (from widget)
        if (!empty($_POST['cloudinary_url'])) {
            $image_url = $_POST['cloudinary_url'];
        }
        // Method 2: Direct image URL input
        elseif (!empty($_POST['image_url'])) {
            $image_url = $_POST['image_url'];
        }
        // Method 3: Traditional file upload (fallback)
        elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = 'uploads/photos/' . $filename;
                }
            }
        }
        
        if (empty($name) || empty($role) || empty($position)) {
            $error = 'Name, Role, and Position are required fields.';
        } else {
            try {
                if ($post_action === 'add') {
                    $sql = "INSERT INTO committee_members (name, role, position, image_url, bio, achievements, year, is_current) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $database->execute($sql, [$name, $role, $position, $image_url, $bio, $achievements, $year, $is_current]);
                    $message = 'Committee member added successfully!';
                    $action = 'list';
                } else {
                    $id = intval($_POST['id']);
                    $sql = "UPDATE committee_members SET name = ?, role = ?, position = ?, image_url = ?, bio = ?, achievements = ?, year = ?, is_current = ? WHERE id = ?";
                    $database->execute($sql, [$name, $role, $position, $image_url, $bio, $achievements, $year, $is_current, $id]);
                    $message = 'Committee member updated successfully!';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($post_action === 'delete') {
        $id = intval($_POST['id']);
        try {
            // Check if member exists first
            $existing = $database->querySingle("SELECT id, name FROM committee_members WHERE id = ?", [$id]);
            if (!$existing) {
                $error = 'Committee member not found.';
            } else {
                $result = $database->execute("DELETE FROM committee_members WHERE id = ?", [$id]);
                if ($result !== false) {
                    $message = 'Committee member "' . htmlspecialchars($existing['name']) . '" deleted successfully!';
                    $action = 'list';
                } else {
                    $error = 'Failed to delete committee member.';
                }
            }
        } catch (Exception $e) {
            $error = 'Error deleting committee member: ' . $e->getMessage();
        }
    }
}

// Get committee members
$members = [];
if ($action === 'list') {
    try {
        $members = $database->queryAll("SELECT * FROM committee_members ORDER BY year DESC, is_current DESC, name ASC");
    } catch (Exception $e) {
        $error = 'Error loading committee members: ' . $e->getMessage();
    }
}

// Get member for editing
$edit_member = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $edit_member = $database->querySingle("SELECT * FROM committee_members WHERE id = ?", [intval($_GET['id'])]);
        if (!$edit_member) {
            $error = 'Member not found.';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = 'Error loading member: ' . $e->getMessage();
        $action = 'list';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Management - DIU Esports Community</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Cloudinary Widget -->
    <script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
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
                
                <a href="committee.php" class="flex items-center space-x-3 px-4 py-3 bg-green-600 bg-opacity-20 border border-green-500 rounded-lg text-green-400">
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
                <h1 class="text-3xl font-orbitron font-bold text-white mb-2">Committee Management</h1>
                <p class="text-gray-400">Manage committee members and leadership</p>
            </div>
            <?php if ($action === 'list'): ?>
            <a href="?action=add" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                <i class="fas fa-plus mr-2"></i>Add Member
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
            <!-- Committee Members List -->
            <div class="bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-600">
                            <tr>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Photo</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Name</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Role</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Position</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Year</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Status</th>
                                <th class="py-3 px-4 text-gray-300 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 px-4 text-center text-gray-400">
                                        No committee members found. <a href="?action=add" class="text-green-400 hover:text-green-300">Add the first member</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($members as $member): ?>
                                    <tr class="border-b border-gray-700 hover:bg-gray-700 hover:bg-opacity-30">
                                        <td class="py-4 px-4">
                                            <?php if ($member['image_url']): ?>
                                                <img src="../<?php echo htmlspecialchars($member['image_url']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-12 h-12 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-600 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 font-medium"><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td class="py-4 px-4"><?php echo htmlspecialchars($member['role']); ?></td>
                                        <td class="py-4 px-4"><?php echo htmlspecialchars($member['position']); ?></td>
                                        <td class="py-4 px-4"><?php echo htmlspecialchars($member['year']); ?></td>
                                        <td class="py-4 px-4">
                                            <?php if ($member['is_current']): ?>
                                                <span class="bg-green-600 text-green-200 px-2 py-1 rounded-full text-xs">Current</span>
                                            <?php else: ?>
                                                <span class="bg-gray-600 text-gray-200 px-2 py-1 rounded-full text-xs">Former</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <a href="?action=edit&id=<?php echo $member['id']; ?>" class="text-blue-400 hover:text-blue-300">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?')">
                                                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
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
                    <?php echo $action === 'add' ? 'Add New Committee Member' : 'Edit Committee Member'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_member['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Full Name *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($edit_member['name'] ?? ''); ?>"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-300 mb-2">Role *</label>
                            <input type="text" id="role" name="role" required
                                   value="<?php echo htmlspecialchars($edit_member['role'] ?? ''); ?>"
                                   placeholder="e.g., President, Vice President, Secretary"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-300 mb-2">Position *</label>
                            <input type="text" id="position" name="position" required
                                   value="<?php echo htmlspecialchars($edit_member['position'] ?? ''); ?>"
                                   placeholder="e.g., Team Captain, Event Coordinator"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-300 mb-2">Year</label>
                            <select id="year" name="year" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($edit_member['year'] ?? date('Y')) == $y ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-300 mb-2">Bio</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Tell us about this member..."
                                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500"><?php echo htmlspecialchars($edit_member['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="achievements" class="block text-sm font-medium text-gray-300 mb-2">Achievements</label>
                        <textarea id="achievements" name="achievements" rows="3" placeholder="List their achievements and accomplishments..."
                                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500"><?php echo htmlspecialchars($edit_member['achievements'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Profile Photo</label>
                        
                        <!-- Image Upload Options -->
                        <div class="space-y-4">
                            <!-- Option 1: Cloudinary Widget -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Upload via Cloudinary</label>
                                <button type="button" id="cloudinary-upload-btn" 
                                        class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Photo to Cloudinary
                                </button>
                                <p class="text-sm text-gray-400 mt-1">Direct upload to Cloudinary (Recommended)</p>
                            </div>
                            
                            <!-- Option 2: Image URL Input -->
                            <div>
                                <label for="image_url" class="block text-sm font-medium text-gray-300 mb-2">Or Enter Photo URL</label>
                                <input type="url" id="image_url" name="image_url" 
                                       value="<?php echo htmlspecialchars($edit_member['image_url'] ?? ''); ?>"
                                       placeholder="https://res.cloudinary.com/your-cloud/image/upload/..."
                                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                                <p class="text-sm text-gray-400 mt-1">Paste any image URL (Cloudinary, external, etc.)</p>
                            </div>
                            
                            <!-- Option 3: Traditional File Upload (Optional) -->
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-300 mb-2">Or Upload File (Optional)</label>
                                <input type="file" id="image" name="image" accept="image/*"
                                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500">
                                <p class="text-sm text-gray-400 mt-1">Traditional file upload (fallback option)</p>
                            </div>
                            
                            <!-- Hidden field for Cloudinary URL -->
                            <input type="hidden" id="cloudinary_url" name="cloudinary_url" value="">
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_current" name="is_current" value="1"
                               <?php echo ($edit_member['is_current'] ?? 1) ? 'checked' : ''; ?>
                               class="w-4 h-4 text-green-600 bg-gray-700 border-gray-600 rounded focus:ring-green-500">
                        <label for="is_current" class="ml-2 text-sm text-gray-300">Current Committee Member</label>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                            <?php echo $action === 'add' ? 'Add Member' : 'Update Member'; ?>
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

        // Cloudinary Upload Widget
        document.getElementById('cloudinary-upload-btn').addEventListener('click', function() {
            cloudinary.openUploadWidget({
                cloudName: 'dn7ucxk8a',
                uploadPreset: 'diu-esports-committee',
                sources: ['local', 'url', 'camera'],
                multiple: false,
                cropping: true,
                croppingAspectRatio: 1, // 1:1 ratio for profile photos
                croppingShowDimensions: true,
                folder: 'diu-esports/committee',
                resourceType: 'image',
                maxFileSize: 10000000, // 10MB
                clientAllowedFormats: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                theme: 'dark',
                text: {
                    en: {
                        upload: 'Upload',
                        loading: 'Loading...',
                        processing: 'Processing...',
                        retry: 'Retry',
                        error: 'Error',
                        done: 'Done',
                        cancel: 'Cancel',
                        close: 'Close',
                        add_more: 'Add more',
                        remove: 'Remove',
                        or: 'or',
                        back: 'Back',
                        next: 'Next',
                        finish: 'Finish',
                        select: 'Select',
                        drag_drop: 'Drag and drop your images here',
                        browse: 'Browse',
                        upload_more: 'Upload more',
                        done_uploading: 'Done uploading',
                        powered_by: 'Powered by Cloudinary'
                    }
                }
            }, function(error, result) {
                if (!error && result && result.event === "success") {
                    console.log('Cloudinary upload successful:', result.info);
                    
                    // Set the Cloudinary URL in the hidden field
                    document.getElementById('cloudinary_url').value = result.info.public_id;
                    
                    // Also set the image URL field
                    document.getElementById('image_url').value = result.info.secure_url;
                    
                    // Show success message
                    const btn = document.getElementById('cloudinary-upload-btn');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check mr-2"></i>Upload Successful!';
                    btn.classList.remove('from-blue-600', 'to-purple-600');
                    btn.classList.add('from-green-600', 'to-green-700');
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.remove('from-green-600', 'to-green-700');
                        btn.classList.add('from-blue-600', 'to-purple-600');
                    }, 3000);
                } else if (error) {
                    console.error('Cloudinary upload error:', error);
                    alert('Upload failed: ' + error.message);
                }
            });
        });

        // Form validation - ensure at least one image method is used
        document.querySelector('form').addEventListener('submit', function(e) {
            const cloudinaryUrl = document.getElementById('cloudinary_url').value;
            const imageUrl = document.getElementById('image_url').value;
            const fileInput = document.getElementById('image');
            
            if (!cloudinaryUrl && !imageUrl && !fileInput.files.length) {
                e.preventDefault();
                alert('Please upload a photo using one of the methods above.');
                return false;
            }
        });
    </script>
</body>
</html>
