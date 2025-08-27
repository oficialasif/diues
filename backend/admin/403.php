<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied | DIU Esports Community</title>
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

    <div class="relative z-10 text-center px-4 max-w-2xl mx-auto">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-600 to-green-500 rounded-full flex items-center justify-center neon-glow">
                <span class="text-3xl font-orbitron font-bold text-white">DIU</span>
            </div>
            <h1 class="text-3xl font-orbitron font-bold text-white mb-2">ESPORTS COMMUNITY</h1>
            <p class="text-gray-400 font-poppins">Admin Panel</p>
        </div>

        <!-- 403 Content -->
        <div class="bg-gray-900 bg-opacity-80 backdrop-blur-sm rounded-2xl p-8 neon-border">
            <div class="text-8xl font-orbitron font-bold text-red-500 mb-6">403</div>
            <h2 class="text-2xl font-orbitron font-bold text-white mb-4">Access Denied</h2>
            <p class="text-gray-300 mb-8">
                You don't have permission to access this resource. Please contact an administrator if you believe this is an error.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="dashboard.php" class="bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 neon-glow">
                    Go to Dashboard
                </a>
                <a href="login.php" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105">
                    Back to Login
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
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-red-400 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-1.5 h-1.5 bg-red-300 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
    </div>
</body>
</html>
