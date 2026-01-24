<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Commercial Pool Equipment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .admin-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .floating-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl w-full flex flex-col lg:flex-row shadow-2xl rounded-2xl overflow-hidden">
            <!-- Left Column - Login Form -->
            <div class="lg:w-1/2 bg-white p-8 lg:p-12">
                <!-- Logo/Brand -->
                <div class="mb-8">
                    <div class="flex items-center justify-center lg:justify-start">
                        <i class="fas fa-shield-halved text-4xl text-blue-600 mr-3"></i>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Admin Portal</h2>
                            <p class="text-sm text-gray-500">Commercial Pool Equipment & Supplies</p>
                        </div>
                    </div>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
                <p class="text-gray-600 mb-8">Sign in to access your dashboard</p>
                
                <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                            <p class="text-blue-700 font-medium">You have been logged out successfully.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['session']) && $_GET['session'] == 'expired'): ?>
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                            <p class="text-yellow-700 font-medium">Your session has expired. Please sign in again.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form id="adminLoginForm" action="api/admin/process_login.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username or Email
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   required 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Enter username or email">
                        </div>
                        <span id="usernameError" class="error-message"></span>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Enter your password">
                            <button type="button" 
                                    id="togglePassword" 
                                    class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span id="passwordError" class="error-message"></span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="remember" 
                                   name="remember"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>
                        <a href="admin_forgot_password.php" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">
                            Forgot password?
                        </a>
                    </div>
                    
                    <button type="submit" 
                            id="submitBtn"
                            class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center justify-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </button>
                </form>
                
                <?php if (isset($_GET['error'])): ?>
                    <div id="serverError" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <p class="text-red-700 font-medium">
                                <?php 
                                $errors = [
                                    'invalid' => 'Invalid username/email or password.',
                                    'locked' => 'Account is temporarily locked due to multiple failed attempts. Try again in 15 minutes.',
                                    'inactive' => 'Your account is inactive. Please contact the administrator.',
                                    'suspended' => 'Your account has been suspended. Please contact the administrator.',
                                    'not_found' => 'No account found with this username or email.',
                                    'empty' => 'Please enter both username and password.',
                                    'system' => 'A system error occurred. Please try again later.'
                                ];
                                echo $errors[$_GET['error']] ?? 'An error occurred. Please try again.';
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Security Notice -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-gray-400 mr-2 mt-0.5"></i>
                        <p class="text-xs text-gray-600">
                            This is a secure area. All login attempts are monitored and logged. 
                            Unauthorized access is prohibited.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Info Panel -->
            <div class="lg:w-1/2 admin-gradient p-8 lg:p-12 text-white flex flex-col justify-center">
                <div class="floating-animation mb-8">
                    <i class="fas fa-chart-line text-8xl opacity-20"></i>
                </div>
                
                <h2 class="text-3xl font-bold mb-6">Admin Dashboard</h2>
                <p class="text-lg mb-8 text-blue-100">
                    Manage your e-commerce platform with powerful tools and insights.
                </p>
                
                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                        <span>Product & inventory management</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                        <span>Order processing & tracking</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                        <span>Customer & user management</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                        <span>Analytics & reporting</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-300 mr-3 mt-1"></i>
                        <span>Content & media library</span>
                    </li>
                </ul>
                
                <div class="pt-8 border-t border-blue-400">
                    <h3 class="text-xl font-bold mb-4">Need Help?</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <i class="fas fa-book text-blue-300 mr-2 mt-1"></i>
                            <span class="text-sm">Documentation</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-headset text-blue-300 mr-2 mt-1"></i>
                            <span class="text-sm">Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('adminLoginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const submitBtn = document.getElementById('submitBtn');
            
            // Toggle password visibility
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Form validation
            loginForm.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
                const serverError = document.getElementById('serverError');
                if (serverError) serverError.style.display = 'none';
                
                // Username validation
                const username = usernameInput.value.trim();
                if (!username) {
                    document.getElementById('usernameError').textContent = 'Username or email is required';
                    isValid = false;
                }
                
                // Password validation
                const password = passwordInput.value;
                if (!password) {
                    document.getElementById('passwordError').textContent = 'Password is required';
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                } else {
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Signing in...';
                }
            });
        });
    </script>
</body>
</html>