<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$database = new Database();
$auth = new Auth($database);

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Check if already logged in
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - DIU Esports Community</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .font-poppins { font-family: 'Poppins', sans-serif; }
        .neon-glow { box-shadow: 0 0 20px #22C55E, 0 0 40px #22C55E; }
        .cyber-bg { background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); }
        .neon-border { border: 2px solid #22C55E; }
        .neon-text { color: #22C55E; text-shadow: 0 0 10px #22C55E; }
    </style>
</head>
<body class="cyber-bg min-h-screen flex items-center justify-center font-poppins">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    
    <!-- Background Grid -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, #22C55E 1px, transparent 0); background-size: 50px 50px;"></div>
    </div>
    
    <div class="relative z-10 w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-600 to-green-500 rounded-full flex items-center justify-center neon-glow">
                <span class="text-3xl font-orbitron font-bold text-white">DIU</span>
            </div>
            <h1 class="text-3xl font-orbitron font-bold text-white mb-2">ESPORTS COMMUNITY</h1>
            <p class="text-gray-400 font-poppins">Admin Panel</p>
        </div>
        
        <!-- Login Form -->
        <div class="bg-gray-900 bg-opacity-80 backdrop-blur-sm rounded-2xl p-8 neon-border">
            <?php if ($error): ?>
                <div class="bg-red-900 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-900 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-300"
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-300"
                        placeholder="Enter your password"
                    >
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow"
                >
                    Sign In
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="../index.php" class="text-gray-400 hover:text-green-400 transition-colors duration-300">
                    ← Back to Website
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                © 2024 DIU Esports Community. All rights reserved.
            </p>
        </div>
    </div>
    
    <!-- Floating Particles -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-blue-500 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
    </div>
</body>
</html>
